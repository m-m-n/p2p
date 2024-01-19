#!/bin/bash

for index in 1; do
  if [ ! -e ../p2p-${index} ]; then
    mkdir ../p2p-${index}
  fi
  tar cf - Dockerfile config docker-compose.yml p2p-src | (cd ../p2p-${index} && tar xf -)
done
