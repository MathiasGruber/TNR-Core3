<?php 
 /* ============== LICENSE INFO START ==============
  * 2005 - 2016 Studie-Tech ApS, All Rights Reserved
  * 
  * This file is part of the project www.TheNinja-RPG.com.
  * Dissemination of this information or reproduction of this material
  * is strictly forbidden unless prior written permission is obtained
  * from Studie-Tech ApS.
  * ============== LICENSE INFO END ============== */ 
?>
<?php

    abstract class fileUploader {

        public static function doUpload($params) {
            
            set_time_limit(5);

            // Set Header
            header('Cache-Control: no-store, no-cache, must-revalidate');

            // Set Constraint Variables
            $maxsize = isset($params['maxsize']) ? $params['maxsize'] : 204800;
            $destination = isset($params['destination']) ? $params['destination'] : "./images/custom/";
            $filename = isset($params['filename']) ? $params['filename'] : 204800;
            $dimX = isset($params['dimX']) ? $params['dimX'] : 100;
            $dimY = isset($params['dimY']) ? $params['dimY'] : 100;
            $img_types = array('gif', 'png');
            $img_mime_types = array();
            foreach($img_types as $type) { array_push($img_mime_types, 'image/'.$type); }
            $extension = explode(".", $_FILES["userfile"]["name"]);

            // Check File Data
            switch($_FILES['userfile']['error']) {
                case(UPLOAD_ERR_OK): break; // File Upload Success
                case(UPLOAD_ERR_INI_SIZE): { // File Exceeds Upload Max File Size in PHP
                    throw new Exception("File exceeds the maximum PHP File Size Specification!");
                } break;
                case(UPLOAD_ERR_FORM_SIZE): { // File Exceed Max File Size in HTML Form
                    throw new Exception("File exceeds the maximum HTML Form Size Specification!");
                } break;
                case(UPLOAD_ERR_PARTIAL): { // File Partially Loaded
                    throw new Exception("File was only partially loaded!");
                } break;
                case(UPLOAD_ERR_NO_FILE): { // No File Loaded
                    throw new Exception("File failed to load!");
                } break;
                case(UPLOAD_ERR_NO_TMP_DIR): { // Missing Temporary Folder
                    throw new Exception("File failed to transfer to temporary folder!");
                } break;
                case(UPLOAD_ERR_CANT_WRITE): { // Failed to Write File to Disk
                    throw new Exception("File failed to write file to disk!");
                } break;
                case(UPLOAD_ERR_EXTENSION): { // PHP Extension Stopped File Upload
                    throw new Exception("File upload was interrupted due to a unknown system extension running!");
                } break; 
                default: { // Unknown Error Listed
                    throw new Exception("File Uploads are currently broken due to unknown reason!");
                } break;
            }

            // Check File Uploaded Or Not
            if ($_FILES['userfile']['size'] <= 0) {
                throw new Exception("You did not upload a file!");
            }

            // Check File Type Format
            if (count($extension) !== 2) {
                throw new Exception("Please follow the format of \"Filename\".\"File Type\" for file uploads!");
            }

            // Check Client Provided MIME File Type Content
            if (!in_array($_FILES["userfile"]["type"], $img_mime_types, true)) {
                throw new Exception("This filetype is not supported. Only GIF & PNG.");
            }

            // Check File Extension to ensure MIME File Type Content was False
            if (!in_array(strtolower(end($extension)), $img_types, true)) {
                throw new Exception("This filetype is not supported. Only GIF & PNG.");
            }

            // Check file size
            if ($_FILES['userfile']['size'] > $maxsize) {
                throw new Exception('The filesize exceeds ' . ($maxsize / 1024) . ' kb');
            }      

            // More checks, will throw exceptions.
            self::isFileMalicious(file_get_contents($_FILES["userfile"]["tmp_name"]));

            // Obtain Image Size (Returns FALSE for Non-Image Data on Failure)
            if (!($imgdata = getimagesize($_FILES['userfile']['tmp_name']))) {
                throw new Exception('The file uploaded is not an image!');
            }

            // Check Image Dimension Specifications
            if ($imgdata[0] > $dimX || $imgdata[1] > $dimY) {
                throw new Exception('The image dimensions exceed ' . $dimX . ' x ' . $dimY . ' pixels');
            }

            // Obtain Generic Part of File Path
            $filePath = $destination . $filename;            
            $data = $_FILES['userfile']['tmp_name'];

            // Check Thoroughly For All Various Images Already Existing
            foreach ($img_types as $val) {
                $s3path = 's3://' . MEDIA_BUCKET . '/' . $filePath . "." . $val;
                if (file_exists($s3path)) {
                    unlink($s3path);
                }
            }

            // Add back extension to filepath
            $filePath .= "." . strtolower(end($extension));

            // Upload image to S3            
            try {
                $upload = $GLOBALS['S3']->upload(MEDIA_BUCKET, $filePath, fopen($data, 'rb'), 'public-read');
            } catch(Exception $e) {
                throw new Exception("An error occurred when moving the uploaded file to the server. Please try again!");
            }

            // Return Success
            return true;
        }

        private static function isFileMalicious($content) {
            
            // Test Image Data doesn't contain PHP Code Tags
            if (strpos($content, '<?php') !== false) { // If Open Tag Found, Check for Close Tag
                if (strpos($content, '?>') !== false) { // If Close Tag Found, Check for Positions
                    if (strpos($content, '<?php') < strpos($content, '?>')) { // If Tags are Enclosed in a way, Throw Error
                        throw new Exception('This file is corrupted with bad data (PHP code injection detected).');
                    }
                }
            }
            elseif (preg_match('/(\s*|\S*)<(\s*)(s|S)(c|C)(r|R)(i|I)(p|P)(t|T)(\s*|>)/', $content)) { // Check for Simple JavaScript Code 
                throw new Exception('This file is corrupted with bad data (JS Code Injection Detected)!');
            }
            elseif (preg_match('/<(?i)script(?:(?!<\/script>).)*<\/(?i)script>/s', $content) 
                || preg_match('/<(?i)script(?:(?!<\/(?i)script>).)*/s', $content) 
                || stripos($content, "<script")) { // Check for Various JavaScript Code
                throw new Exception("This file is corrupted with bad data (JS Code Injection Detected)!");
            }
            elseif (preg_match('/\/usr\/local\/bin\//', $content)) { // Check for Bin Code
                throw new Exception("This file is corrupted with bad data (Bin Injection Detected)!");
            }
            
        }

        // An upload form
        public static function uploadForm($params) {
            
            // Set variables
            $maxsize = isset($params['maxsize']) ? $params['maxsize'] : 204800;
            $image = isset($params['image']) ? $params['image'] : "./images/default_avatar.png";
            $dimX = isset($params['dimX']) ? $params['dimX'] : 100;
            $dimY = isset($params['dimY']) ? $params['dimY'] : 100;
            $description = isset($params['description']) ? $params['description'] : "";
            $title = isset($params['subTitle']) ? $params['subTitle'] : "";

            // Show the form
            $GLOBALS['template']->assign('maxsize', $maxsize);
            $GLOBALS['template']->assign('image', $image);
            $GLOBALS['template']->assign('dimX', $dimX);
            $GLOBALS['template']->assign('dimY', $dimY);
            $GLOBALS['template']->assign('description', $description);
            $GLOBALS['template']->assign('subTitle', $title);

            // Load the template
            $GLOBALS['template']->assign('contentLoad', './templates/fileUpload.tpl');
            
        }
        
    }