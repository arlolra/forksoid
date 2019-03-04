/** @module */

'use strict';

const path = require('path');
const childProcess = require('child_process');
const TokenHandler = require('./TokenHandler.js');
const { TokenUtils } = require('../../utils/TokenUtils.js');

/**
 * Wrapper that invokes a PHP token transformer to do the work
 *
 * @class
 * @extends module:wt2html/tt/TokenHandler
 */
class PHPTransformer extends TokenHandler {
	constructor(manager, name, options) {
		super(manager, options);
		this.transformerName = name;
	}

	processTokensSync(env, tokens, traceState) {
		if (!(/^\w+$/.test(this.transformerName))) {
			console.error("Transformer name failed sanity check.");
			process.exit(-1);
		}

		const pipelineOpts = JSON.stringify(this.options) + "\n";
		const inputToks = tokens.map(t => JSON.stringify(t)).join('\n');
		const res = childProcess.spawnSync("php", [
			path.resolve(__dirname, "../../../bin/runTransform.php"),
			this.transformerName,
		], {
			input: pipelineOpts + inputToks
		});

		const stderr = res.stderr.toString();
		if (stderr) {
			console.error(stderr);
		}

		const toks = res.stdout.toString().split("\n").map((str) => {
			return str ? JSON.parse(str, (k, v) => TokenUtils.getToken(v)) : "";
		});

		return toks;
	}
}

if (typeof module === "object") {
	module.exports.PHPTransformer = PHPTransformer;
}