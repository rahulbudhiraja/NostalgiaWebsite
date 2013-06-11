<?php
include_once "fbaccess.php";

// Useful Function with PHP and CURL to save the image .Specify the Image URL followed by the File path on disk 

function save_image($img,$fullpath){
    $ch = curl_init ($img);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_BINARYTRANSFER,1);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
  	curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
  	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  
  
    $rawdata=curl_exec($ch);
    curl_close ($ch);

    $fp = fopen($fullpath,'w');
    fwrite($fp, $rawdata);
    fclose($fp);
}

// If logged in,Proceed knowing you have a logged in user who has a valid session.

if($user){
	
	// Declare the 2 Arrays which will hold the untagged and Tagged images ..
	
	$untaggedImageData=array();
	$taggedImageData=array();
	
	$token= $facebook->getAccessToken();
    $facebook->setAccessToken($token);
	
	
	// Create a directory for the users info.
	mkdir($user,0777);

	/* Downloading Profile Picture and Names */
	
	// Saving the name in a file called name.txt 
	
	$name_data=$facebook->api('/me?fields=name');
	$name=$name_data['name'];
	$id=$name_data['id'];
	
	$myFile = "$user/name.txt";
	$fh = fopen($myFile, 'w') or die("can't open file");
	
	fwrite($fh,$name);
	fclose($fh);
	
	// Saving the Profile Picture ..
	
	$profile_picdata=$facebook->api('/me?fields=picture.width(800).height(800)');
	$profile_pic=$profile_picdata['picture']['data']['url'];
	
	$profile_pic_location="$user/profilepic.jpg";
	save_image($profile_pic,$profile_pic_location);
	
	$output=null;
	
	// Execute the BadgeCreator Application ,Comment this out if you need a badge .
	
	//$outputty=system("open /Users/rahulbudhiraja/Work/of_v0073_osx_release/apps/myApps/BadgeCreator/bin/BadgeCreatorDebug.app --args $id");
	
	$albums = $facebook->api('/me/albums?fields=id');  // This will give a data structure containing details of the Albums of the pictures that are uploaded by the user 
	$pictures = array();
	
	    foreach ($albums['data'] as $album) {
	      $pics = $facebook->api('/'.$album['id'].'/photos'); 
	      $pictures[$album['id']] = $pics['data']; // This gives the set of all photos in an album ....
		
	    }
				
	    $imagecounter=0;
		$albumcounter=0;
  
		// Ranking Pictures ..	
	
  
		    //display the pictures url
		    foreach ($pictures as $album) {    // from each pictures data structure ,extract album
		      
			$names=array();
				
			  //Inside each album
		      foreach ($album as $image) {  // take the image from each album 
		      
		  			  $tags=0;
				
					  $pic_array=$image['images']; // This is the image data structure ..
					  
					  $img_key=0;
					  
					  $firstpic=$pic_array[$img_key];
					  $output=$firstpic['source'];
					  
				  	  //$string="$user/{$imagecounter}.jpg";
					  //save_image($output,$string); 
					  
					  // Getting the likes ...
					  
				      $likes=0; 
					  $image_id=$image['id'];
					  $likes_data = $facebook->api('/'.$image_id.'/likes?limit=10000000');
	  
					  foreach($likes_data['data'] as $actuallikedata)
					  {
						  $likes++;
		  
					  }
					  
					  // Getting the Tags ..
					  
					  foreach($image['tags']['data'] as $taggedImage)
					  {

						  $names[$tags]=$taggedImage['name'];
						  $tags++;	
						  
					  }
					  

					  $numcomments=0;
					  
					  // Getting the Comments
	  
					  foreach($image['comments']['data'] as $comments)
					  {
						  $numcomments++;
		 
					  }
					  
					  /* Simple Ranking Algorithm */
					  
					  /* 
					  * Let l-number of likes,c-number of comments,t-no. of tags,s-Rankscore
					  * if(t>0) 
					  *	 score=l+2c; (Our logic: it is easy to get likes and difficult to get the comments 
					  *  for an image you were tagged in as it requires more effort.Any comments on the image should
					  *  thus have a greater weightage)
					  *	 else score =(l/2)+c; ( Our Logic: Untagged images are usually  profile pictures or trips,or maybe even some that you take at a party,or maybe even food !.These pictures get too much likes so l+2c is not a good measure since ,the likes are always overshadowed by the comments,To reduce the score contributed by likes ,we divided the likes by half )
					  *  The algorithm can easily be changed to suit your preference ,but we have found that the above algorithm works reasonably well .
					  */
					  
					  
					  if($tags>0)
  					  {
  						  if($tags>8) // If the number of tags are greater than 8,the photo could be a spam so score=0 
  							 continue;// $score=0;
  						  else $score=$likes+2*$numcomments;
						  
	  					  $taggedImageData[]=array(
	  					  		"score"=>$score,
	  							"imageindex"=>$imagecounter,
	  					  		"albumnum"=>$albumcounter,
	  							"url"=>$output
	  					  	);
  					  }
					  
  					  else {
						  $score=($likes/2)+$numcomments;
	  					  $untaggedImageData[]=array(
	  					  		"score"=>$score,
	  							"imageindex"=>$imagecounter,
	  					  		"albumnum"=>$albumcounter,
	  							"url"=>$output
	  					  	);
					  }

  					  $imagecounter++;
	  
			}
			$albumcounter++;
		}
	
// Now We have to get the images that the user is tagged in but are uploaded by other users ..
	
$tagged_photos=$facebook->api('me/photos');  // This will give a data structure containing details of the Albums of pictures that are uploaded by the user 


	$tags=$facebook->api('me/photos?fields=tags');	
	$pictures = array();
	$tagcounter=1;

	foreach ($tagged_photos['data'] as $taggedpics)
	{
		
	  $friend_id=$taggedpics['id']; // the friends id who uploaded the picture 
	  $pic_array=$taggedpics['images'];
	
  	  // Getting THE likes
	   
      $likes=0; 
  	
  	  $likes_data = $facebook->api('/'.$friend_id.'/likes?limit=10000000');
  
  	  foreach($likes_data['data'] as $actuallikedata)
  	  {
  		  $likes++;
	  
  	  }
	  
	$key=0;
	$firstPic=$pic_array[$key];
	
	$pic=$firstPic['source'];

	///var_dump($firstPic);
	//echo $firstPic;
	
	// Uncomment the next 2 lines if you want to download all of your pictures on facebook uploaded by other users but you are included in them.
	
	$path="$user/{$imagecounter}.jpg";
	// save_image($pic,$path);
  	
	
	$tags=0;
		
	// Getting Tags 
	foreach ($taggedpics['tags']['data'] as $taggedfriends)
	{	
		$tags++;
		
    }	
		
	  $numcomments=0;
	  
	  // Comments 

	  foreach($taggedpics['comments']['data'] as $comments)
	  {
		  $numcomments++;
 
	  }
	  
	  // Similar algorithm,as before 
	  
				  if($tags>0)
					  {
						  if($tags>8)
							  continue;
						  else $score=$likes+2*$numcomments;
					  
  					  $taggedImageData[]=array(
  					  		"score"=>$score,
  							"imageindex"=>$imagecounter,
  					  		"albumnum"=>$albumcounter,
  							"url"=>$pic
  					  	);
					  }
			  
					  else {
					  $score=($likes/2)+$numcomments;
  					  $untaggedImageData[]=array(
  					  		"score"=>$score,
  							"imageindex"=>$imagecounter,
  					  		"albumnum"=>$albumcounter,
  							"url"=>$pic
  					  	);
				  }
		
  	$imagecounter++;
		
}



rsort($untaggedImageData);rsort($taggedImageData); // Sorting the Images in descending order of scores 

// Debug Statements 

// echo "<br />";
// echo "Size of the untagged Array ".count($untaggedImageData
// echo "<br 
// echo "Size of the tagged Array ".count($taggedImageData);

mkdir($user.'/taggedImages',0777);
mkdir($user.'/untaggedImages',0777);

/* Now we have to download the top 100 images and also create an xml file with details and ranking of the images .

The Xml Structure is :

 ImageList
    |
     -Untagged
	      |
		   -Image
			   |
				-Score
				-AlbumNumber
     -Tagged
	      |
		   -Image
			   |
				-Score
				-AlbumNumber
	
*/	
	
	
	
// Create the XML Tree	and elements 

$row=0;

$pictureDataTree = new DOMDocument('1.0', 'UTF-8');
$pictureXML=$pictureDataTree->createElement("xml");
$pictureXML=$pictureDataTree->appendChild($pictureXML);

$mainElement=$pictureDataTree->createElement("ImageList");
$mainElement=$pictureXML->appendChild($mainElement);

$untaggedImages=$pictureDataTree->createElement("Untagged");
$untaggedImages=$mainElement->appendChild($untaggedImages);

$maxImagestoDownload=100; // Change this number if you want to download more images .
 
$numberofUntaggedImagestoDownload=$maxImagestoDownload/2;
$numberoftaggedImagestoDownload=$maxImagestoDownload/2;

/* We try to download 50 untagged and 50 tagged images to have a nice balance,but if we have <50 on either array,we try to adjust the number of images that are to be downloaded.
   For eg: there are 25 untagged images,75 tagged images will be downloaded 
*/

if(count($untaggedImageData)<$numberofUntaggedImagestoDownload)
	$numberoftaggedImagestoDownload+=($numberofUntaggedImagestoDownload-count($untaggedImageData));

else if(count($taggedImageData)<$numberoftaggedImagestoDownload)
	$numberofUntaggedImagestoDownload+=$numberoftaggedImagestoDownload-count($taggedImageData);

// echo "Reached";

// Downloading the untaggedImages ..

while($row<count($untaggedImageData)&&$row<=$numberofUntaggedImagestoDownload)
{
  	$imageTag=$pictureDataTree->createElement("Image"); 
  	$imageTag=$untaggedImages->appendChild($imageTag);
	
	
	// echo $untaggedImageData[$row]["score"]." and the image number is ".$untaggedImageData[$row]["imageindex"];
	//echo "<br />";
		
	$path="$user/untaggedImages/$row.jpg";
	$imgSource=$untaggedImageData[$row]["url"];
	
	save_image($imgSource,$path);
	
	/// Adding the Image Information to the XML...
	
    $imageTag->appendChild($pictureDataTree->createElement('Score',$untaggedImageData[$row]["score"]));
    $imageTag->appendChild($pictureDataTree->createElement('AlbumNumber',$untaggedImageData[$row]["albumnum"]));
  
	$row++;
	
}



$taggedImagesXML=$pictureDataTree->createElement("Tagged");
$taggedImagesXML=$mainElement->appendChild($taggedImagesXML);

// echo "<br />";echo "<br />";echo "<br />";echo "<br />";echo "<br />";echo "<br />";

$taggedImageCount=0;

// Downloading the TaggedImages ..


	while($taggedImageCount <count($taggedImageData)&&$taggedImageCount<=$numberoftaggedImagestoDownload)
	{
		
	  	$imageTag= $pictureDataTree->createElement("Image"); 
	  	$imageTag=$taggedImagesXML->appendChild($imageTag);
	
		// Debug Statements 
		
		// echo $taggedImageData[$taggedImageCount]["score"]." and the image number is ".$taggedImageData[$taggedImageCount]["imageindex"];
			// echo "<br />";
		
		$path="$user/taggedImages/$taggedImageCount.jpg";
		$imgSource=$taggedImageData[$taggedImageCount]["url"];
	
		save_image($imgSource,$path);
		
		// Adding the Image Information to the XML like score and albumnumber .
	
	    $imageTag->appendChild($pictureDataTree->createElement('Score',$taggedImageData[$taggedImageCount]["score"]));
	    $imageTag->appendChild($pictureDataTree->createElement('AlbumNumber',$taggedImageData[$taggedImageCount]["albumnum"]));
	
		$taggedImageCount++;
	
	}	
	
	$path=getcwd()."/$user/imagedata.xml";

	$pictureDataTree->save("$path");
	
	 // $folderPath=getcwd()."/$user"; // To check the path 

	 // In case you want to apply a vignette filter using ImageMagick,you can uncomment this and change the path accordingly.
	 
	// $otputty2=system("open /Users/rahulbudhiraja/Work/of_v0073_osx_release/apps/myApps/NostalgiaVignetteExecution/bin/NostalgiaVignetteExecutionDebug.app --args $user");
	
	// logout of the user's account when finished download the images
	include 'logout.php';


		
	
}

?>