#!/usr/bin/env bash
# Geçici tanı: gerçek Chrome (DevTools bağlı değil, gerçek fare) dev.pebuc.site'ta TR→EN düğmesine basar.
# $1 = sayfa açıldıktan kaç sn sonra fare düğmeye gelir, $2 = fare düğmede kaç sn bekler, $3 = etiket
set -u
WAIT=$1; HOVER=$2; TAG=$3
D=$(mktemp -d); NL=/tmp/netlog-$TAG.json
google-chrome --kiosk --no-first-run --no-default-browser-check --disable-features=Translate \
  --user-data-dir=$D --log-net-log=$NL https://dev.pebuc.site/tr/ >/dev/null 2>&1 &
CP=$!
T0=$(date +%s.%N)
t() { printf '%5.1f' "$(echo "$(date +%s.%N) - $T0" | bc)"; }
title() { xdotool search --onlyvisible --class google-chrome getwindowname 2>/dev/null | tail -1; }
END=$(echo "$WAIT + $HOVER + 40" | bc)
clicked=0; last=""
while (( $(echo "$(t) < $END" | bc) )); do
  now=$(t); cur=$(title)
  [ "$cur" != "$last" ] && echo "[$TAG] $now başlık: $cur" && last=$cur
  if [ $clicked = 0 ] && (( $(echo "$now >= $WAIT" | bc) )); then
    xdotool mousemove 1301 30; echo "[$TAG] $(t) fare EN üstünde"; sleep $HOVER
    xdotool click 1; echo "[$TAG] $(t) TIKLANDI"; clicked=1
    import -window root /tmp/shot-$TAG-click.png
  fi
  sleep 0.25
done
import -window root /tmp/shot-$TAG-end.png
kill -TERM $CP; sleep 3
python3 - "$NL" "$TAG" <<'PY'
import json,sys
try: d=json.load(open(sys.argv[1]))
except Exception as e:
    s=open(sys.argv[1]).read().rstrip().rstrip(',')
    if not s.endswith(']}'): s+=']}'
    d=json.loads(s)
c=d['constants']; et={v:k for k,v in c['logEventTypes'].items()}
base=None
for e in d['events']:
    tm=int(e['time']); base=base or tm
    n=et.get(e['type'],'?'); p=e.get('params',{})
    u=p.get('url','')
    if n in('URL_REQUEST_START_JOB','REQUEST_ALIVE') and u and ('/en/' in u.split('pebuc.site')[-1][:4] or '/tr/' in u.split('pebuc.site')[-1][:4]) and u.rstrip('/').endswith(('/en','/tr')):
        print(f"[{sys.argv[2]}] net {(tm-base)/1000:6.2f}s {n} src={e['source']['id']} {u} {p.get('method','')}")
    if n=='HTTP_TRANSACTION_READ_RESPONSE_HEADERS':
        h=p.get('headers',[])
        if h and any(x in str(h) for x in ['text/html']): print(f"[{sys.argv[2]}] net {(tm-base)/1000:6.2f}s RESP src={e['source']['id']} {h[0]}")
PY
