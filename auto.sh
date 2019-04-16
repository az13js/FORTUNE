#!/bin/bash

while :
do
    php artisan spider >>/dev/null 2>&1
done
