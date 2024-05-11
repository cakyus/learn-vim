#!/bin/sh

if [ ! $HOME/.vim/plugin ] ; then
	>&2 echo "vim plugin dir not exits"
	exit 1
fi

cp -v *.vim $HOME/.vim/plugin/

