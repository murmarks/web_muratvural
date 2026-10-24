#!/usr/bin/env bash
# Geçici tanı: gerçek Chrome (DevTools bağlı değil, gerçek fare) dev.pebuc.site'ta TR→EN düğmesine basar.
# $1 = sayfa açıldıktan kaç sn sonra fare düğmeye gelir, $2 = fare düğmede kaç sn bekler, $3 = etiket
set -u
WAIT=$1; HOVER=$2; TAG=$3
D=$(mktemp -d); NL=/tmp/netlog-$TAG.json
google-chrome --kiosk --no-first-run --no-default-browser-check --disable-features=Translate \
  --user-data-dir=$D https://${H0:-dev.pebuc.site}/tr/ >/dev/null 2>&1 &
CP=$!
T0=$(date +%s.%N)
t() { printf '%5.1f' "$(echo "$(date +%s.%N) - $T0" | bc)"; }
title() { xdotool search --onlyvisible --class google-chrome getwindowname 2>/dev/null | tail -1; }
END=$(echo "$WAIT + $HOVER + 30" | bc)
clicked=0; last=""
while (( $(echo "$(t) < $END" | bc) )); do
  now=$(t); cur=$(title)
  [ "$cur" != "$last" ] && echo "[$TAG] $now başlık: $cur" && last=$cur
  if [ $clicked = 0 ] && (( $(echo "$now >= $WAIT" | bc) )); then
    import -window root /tmp/pre-$TAG.png
    XY=$(tesseract /tmp/pre-$TAG.png - --psm 11 tsv 2>/dev/null | awk -F'\t' '$12=="EN" && $8<150 {print int($7+$9/2), int($8+$10/2); exit}')
    echo "[$TAG] OCR EN konumu: [$XY] | üst satır: $(tesseract /tmp/pre-$TAG.png - --psm 11 tsv 2>/dev/null | awk -F'\t' '$8<120 && $12!="" {printf "%s@%s,%s ", $12,$7,$8}')"
    [ -z "$XY" ] && XY="1286 30"
    xdotool mousemove $XY; echo "[$TAG] $(t) fare EN üstünde ($XY)"; sleep $HOVER
    xdotool click 1; echo "[$TAG] $(t) TIKLANDI"; clicked=1
    import -window root /tmp/shot-$TAG-click.png
  fi
  sleep 0.25
done
import -window root /tmp/shot-$TAG-end.png
kill -TERM $CP; sleep 3
echo "[$TAG] bitti"
