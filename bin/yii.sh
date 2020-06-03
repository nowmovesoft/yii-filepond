#!/bin/bash

docker-compose --file ../docker-compose.yml run --rm web vendor/bin/yii $*
