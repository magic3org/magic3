#!/bin/bash

# modify directry names.
VOICE=hts_voice_nitech_jp_atr503_m001-1.04
DIC=open_jtalk_dic_utf_8-1.04

/usr/local/bin/open_jtalk \
-s 48000 \
-z 6000 \
-p 240 \
-a 0.55 \
-jm 1.5 \
-l \
-td $VOICE/tree-dur.inf \
-tf $VOICE/tree-lf0.inf \
-tm $VOICE/tree-mgc.inf \
-md $VOICE/dur.pdf \
-mf $VOICE/lf0.pdf \
-mm $VOICE/mgc.pdf \
-df $VOICE/lf0.win1 \
-df $VOICE/lf0.win2 \
-df $VOICE/lf0.win3 \
-dm $VOICE/mgc.win1 \
-dm $VOICE/mgc.win2 \
-dm $VOICE/mgc.win3 \
-ef $VOICE/tree-gv-lf0.inf \
-em $VOICE/tree-gv-mgc.inf \
-cf $VOICE/gv-lf0.pdf \
-cm $VOICE/gv-mgc.pdf \
-k  $VOICE/gv-switch.inf \
-x $DIC \
-ow _test.wav \
-ot _log.txt \
test.txt
