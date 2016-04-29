#!/bin/bash

cd /home/pi/raspberry-spi

sudo hciconfig hci0 piscan

sudo python spi.py
