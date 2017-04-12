#!/bin/bash

# Perform a basic crash test.
# This used to be the
# `parsoidsvc-{repository}-parse-tool-check-trusty`
# test on jenkins. (T141481)

BIN=$(dirname $0)

# If any of these commands fail, something is wrong!
set -ev

echo "Foo" | node $BIN/parse.js --wt2html
echo "Foo" | node $BIN/parse.js --wt2html --pageBundle
echo "Foo" | node $BIN/parse.js --wt2wt
echo "Foo" | node $BIN/parse.js --html2wt
echo "Foo" | node $BIN/parse.js --html2html

# Check --selser too!
TMPWT=$(tempfile -s wt)
TMPORIG=$(tempfile -s orig)
TMPEDIT=$(tempfile -s edit)
TMPPB=$(tempfile -s pb)
# inline data-parsoid
echo "<p>foo</p><p>boo</p>" | tee $TMPWT | node $BIN/parse.js | tee $TMPORIG |
    sed -e "s/foo/bar/g" > $TMPEDIT
node $BIN/parse.js --selser --oldtextfile $TMPWT --oldhtmlfile $TMPORIG < $TMPEDIT

# data-parsoid in separate files
node $BIN/parse.js --pboutfile $TMPPB --contentVersion 2.0.0 < $TMPWT |
    tee $TMPORIG | sed -e "s/foo/bar/g" > $TMPEDIT
node $BIN/parse.js --pbinfile $TMPPB --selser \
    --oldtextfile $TMPWT --oldhtmlfile $TMPORIG < $TMPEDIT

# clean up
/bin/rm $TMPWT $TMPORIG $TMPEDIT $TMPPB
