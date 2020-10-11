# Dynamic YouTube Video Name
With this `PHP` project you can change your YouTube video name dynamically. This code will change your YouTube video name as per your video's views and likes. 

## Video Tutorial:

https://www.youtube.com/watch?v=5W1LkMzzjkQ

## PHP Version:

1. Requires PHP version 7.1 or greater.

## Library Requirements:

>**Note:** Library requirements are already fulfilled in this project. But in case you want to get the updated libraries, you can get those by following steps.

1. Install composer (https://getcomposer.org)

2. On the command line, change directory to your root project directory (eg: /var/www/html/)

3. Require the google/apiclient library and to install that run a command: **`composer require google/apiclient:~2.0`**

## Code Working

1. Retrieves the statistics video resource by calling the **`youtube.videos.list`** method.

2. And then it compare the new views and likes count returned by above API with old similar data stored in **`data.json`** file.

3. If old and new data doesn't match it retrieving the snippet video resource by calling the **`youtube.videos.list`**.

4. Then finally update the title of video resource by calling the **`youtube.videos.update`** method.


## Security:

1. For improved security you must place your **`key.json`** file outside the directories accessible from web. After that update **`index.php`** file with the correct path. (eg: place key.json file in /var/www/)

## Further Reference

1. For sample implementations in other languages visit [here](https://github.com/youtube/api-samples): 
2. For more sample examples in PHP you can visit [here](https://github.com/youtube/api-samples/tree/master/php):
