#!/bin/bash

while true
do
    # Ask for input
    read -p "Enter url (or type 'exit' to quit): " url

    # Exit condition
    if [[ "$url" == "exit" ]]; then
        echo "Bye!"
        break
    fi

    # Run Laravel command with input
    php artisan tg:webhook "$url"
done
