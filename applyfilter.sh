#!/bin/bash

# Make a Folder Called Converted_Files ...

cd "$(dirname "/Applications/MAMP/htdocs/NostalgiaRoom/$1/taggedImages")";

mkdir 'converted_files';
mkdir 'converted_files/taggedImages';
mkdir 'converted_files/untaggedImages';

# Based on the random number generated ,assign a filter .
# $path={$PWD}+/vignette.sh;
echo ${PWD};

# $arguement='-i 50 -o 100 -c black -a 50 blah.png 1.jpg';
output='blah.png';
output2='converted_files/';
argument='-i 50 -o 100 -c black -a 50';

for img in `ls taggedImages/*.jpg`
do 
# Generate a Random Number ....
	#r=$(( $RANDOM % 3 +1));
	#echo $r;
	#if((r==1));then
		#sh vignette2.sh "$img" "$output2$img";
		#elif((r==2)); then sh ortoneffect.sh "$img" "$output2$img";
		#elif((r==3));then sh toycamera.sh "$img" "$output2$img";
		 sh "../toycamera.sh" "$img" "$output2$img";
	echo $img;
	#fi
done

for img in `ls untaggedImages/*.jpg`
do 
		 sh '../toycamera.sh' "$img" "$output2$img";
	echo $img;
	#fi
done

# toycamera is expensive ...and color boost -not to be used ...