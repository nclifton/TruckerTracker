#!/usr/bin/env bash

mongorestore --db trucker_tracker --drop -u trucker_tracker -p 6iSgcH2eNE --authenticationDatabase admin -v dbbackups/$1/trucker_tracker