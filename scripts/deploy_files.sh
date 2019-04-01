#!/bin/bash

scp ./* root@178.128.72.34:/root;
scp -r ./app ./bootstrap ./config ./database ./public ./resources ./routes ./storage root@178.128.72.34:/root;