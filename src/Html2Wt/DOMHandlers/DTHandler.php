<?php
declare( strict_types = 1 );

namespace Parsoid\Html2Wt\DOMHandlers;

use DOMElement;
use DOMNode;
use Parsoid\Html2Wt\SerializerState;
use Parsoid\Utils\DOMDataUtils;
use Parsoid\Utils\DOMUtils;
use Parsoid\Utils\WTUtils;

class DTHandler extends DOMHandler {

	public function __construct() {
		parent::__construct( true );
	}

	/** @inheritDoc */
	public function handle(
		DOMElement $node, SerializerState $state, bool $wrapperUnmodified = false
	): ?DOMNode {
		$firstChildElement = DOMUtils::firstNonSepChild( $node );
		if ( !DOMUtils::isList( $firstChildElement )
			 || WTUtils::isLiteralHTMLNode( $firstChildElement )
		) {
			$state->emitChunk( $this->getListBullets( $state, $node ), $node );
		}
		$liHandler = function ( $state, $text, $opts ) use ( $node ) {
			return $state->serializer->wteHandlers->liHandler( $node, $state, $text, $opts );
		};
		$state->singleLineContext->enforce();
		$state->serializeChildren( $node, $liHandler );
		$state->singleLineContext->pop();
		return $node->nextSibling;
	}

	/** @inheritDoc */
	public function before( DOMElement $node, DOMNode $otherNode, SerializerState $state ): array {
		return [ 'min' => 1, 'max' => 2 ];
	}

	/** @inheritDoc */
	public function after( DOMElement $node, DOMNode $otherNode, SerializerState $state ): array {
		if ( $otherNode->nodeName === 'dd'
			&& $otherNode instanceof DOMElement // for static analyzers
			&& ( DOMDataUtils::getDataParsoid( $otherNode )->stx ?? null ) === 'row'
		) {
			return [ 'min' => 0, 'max' => 0 ];
		} else {
			return $this->wtListEOL( $node, $otherNode );
		}
	}

	/** @inheritDoc */
	public function firstChild( DOMElement $node, DOMNode $otherNode, SerializerState $state ): array {
		if ( !DOMUtils::isList( $otherNode ) ) {
			return [ 'min' => 0, 'max' => 0 ];
		} else {
			return [];
		}
	}

}
