#!/usr/bin/env bash

mongodump --db trucker_tracker -u trucker_tracker -p 6iSgcH2eNE --authenticationDatabase admin --out dbbackups/$1