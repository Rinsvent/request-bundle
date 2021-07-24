#!/bin/bash

FULL_PROJECT_NETWORK=$(docker network ls | grep full-project)
if [ -z "$FULL_PROJECT_NETWORK" ]
then
    docker network create full-project --subnet=192.168.221.0/25
fi

