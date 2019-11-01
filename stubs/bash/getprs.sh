#!/bin/bash

FILE="${HOME}/.steward/locks/.bitbucket-pull-requests"

mkdir -p ${HOME}/.steward/locks

if [ ! -f ${FILE} ]; then
    touch -t 201203101513 ${FILE}
fi

if test `find ${FILE} -mmin -5`; then
    cat ${FILE}
    exit 0
fi

stew bitbucket:open ponbikedmp/b2c-gazelle --username="i.kada@me.com" --token="FCeWtdRjE2RdU+PPXtvPPQc" --raw > ${FILE}
