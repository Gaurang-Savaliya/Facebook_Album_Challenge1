

<!DOCTYPE html>
<html lang="en">
    <?php
     ob_start();
			@session_start();
    ?>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
	    <title>Rtdownloader</title>
		
		<link rel="shortcut icon" href="images/favicon.png" />
		
		<!-- Bootstrap -->
		<link rel="stylesheet" type="text/css" href="css/bootstrap.css">
		<!-- fancybox css File -->
		<link rel="stylesheet" type="text/css" href="source/jquery.fancybox.css" media="screen" />
		<!-- Main css File -->
		<link rel="stylesheet" type="text/css" href="css/style.css">
		<!-- Mobile Responsive CSS -->
		<link rel="stylesheet" type="text/css" href="css/responsive.css">
		
	</head>
	<body>
	
	<!-- Header Logo & Menu Strat -->	
	
	<section id="header" class="header-color">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="logo text-center">
						<h2><a href="index.php">Rtdownloader</a></h2>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Header Logo & Menu End -->	
	
	<section id="galley-listWrap">
		<div class="section-padding">
			<div class="container">
				<div class="row">
				<div class="col-md-2 absolute"> 
				
					<input type="button" class="btn btn-info"  onclick="window.location = 'downloadall.php'"  value="Download All Albums">
						<br/>
						<br/>
					<input type="button" class="btn btn-info"  onclick="window.location = 'backupall.php'"  value="Backup All Albums">
      
			</div>
	<div class="col-md-8 text-left"> 
	  <?php
	     
			
			$tmp = $_SESSION['userData'];
			echo "<h3><u> " . $tmp['name'] ."</u></h3>";
             $total = count( $tmp['albums'] );
			 ?>
			 <h2>Select albums to upload Albums on your Google Drive </h2>
			 
			 <form action=# method="post">
			 
			 
			 <?php
			 for($i=0;$i<$total;$i++)
			 { 
		          
		           $aid=$tmp['albums'][$i]['id'];
				?>
				  <div class="col-md-4 col-sm-4 col-xs-12">
						<div class="img1">
				       <?php   echo "<b>" . $tmp['albums'][$i]['name']. "</b>";?>
				    <input type="checkbox" name="images[]" value="<?php echo $aid;?>">    
				
				<a href="show.php?aid=<?php echo $aid; ?>">
				 <img src="<?php echo $tmp['albums'][$i]['picture']['url']; ?>" class="img img-responsive">
				</a>
				
				<?php
			
				 echo "Total Photos:".$tmp['albums'][$i]['count'];
				 echo "</div></div>";
 
			 }
			 ?>
			 
			 <br/>
			 <br/>
			 <div class="row"><div class="col-md-12 text-center"> 
	           <input type="submit" value="Upload Selected Albums" class="btn btn-primary"/>
			 </div>	 
			 			 	 </div>	 
     <?php            
               $mainDirectory = "facebook_".$tmp['name']."_albums";
		$mainDirectory = str_replace(' ','',$mainDirectory);
		$path = $mainDirectory;

	
	
		if (!is_dir($mainDirectory)) {
			mkdir($mainDirectory,0777,true);
		}
		if(isset($_REQUEST['images']))
		{
			//for individual selected albums
			foreach($_POST['images'] as $sel)
			{
				$counter=0;
				
				foreach($tmp['albums'] as $album)
				{
					$id = $tmp['albums'][$counter]['id'];
					$albumName = $tmp['albums'][$counter]['name'];
					$mainpath = $path;
					if($id == $sel)
					{
						$albumName = str_replace(' ','',$albumName);
						$albumPath = $mainpath."/".$albumName;
						// checks the directory is available or not if not the create 
						if (!is_dir($albumPath)) {
							mkdir($albumPath,0777,true);
						}	
						// image download
						$imagePath = $albumPath."/";
						foreach($album['photos'] as $item)
						{
							file_put_contents($imagePath.$item['id'].'.jpg', file_get_contents($item['images'][0]['source']));
						
						}
				
					}
					$counter++;
				}
			}
          
          			
       $folderName = $path;
	   
	//for creating and downloading zip file
	function createZipFile($folderName)
	{
		//$folderName= "zipFolderDemo";
		$filepath =  $_SERVER['DOCUMENT_ROOT']."/".$folderName;
		$rootPath = realpath($filepath);

		// Initialize archive object
		$zip = new ZipArchive();
		$zipfilename = $folderName.'.zip';
		$zip->open('files/'.$zipfilename, ZipArchive::CREATE | ZipArchive::OVERWRITE);

		// Create recursive directory iterator
		/** @var SplFileInfo[] $files */
		$files = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($rootPath),
			RecursiveIteratorIterator::LEAVES_ONLY
		);
	
		foreach ($files as $name => $file)
		{
			// Skip directories (they would be added automatically)
			if (!$file->isDir())
			{
				// Get real and relative path for current file
				$filePath = $file->getRealPath();
				$relativePath = substr($filePath, strlen($rootPath) + 1);

				// Add current file to archive
				$zip->addFile($filePath, $relativePath);
			}
		}

		// Zip archive will be created only after closing object
		$zip->close();
		//return $zipfilename;
		
	}
	
	
	function deleteDir($dirPath) {
        if (! is_dir($dirPath)) {
            throw new InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            if (is_dir($file)) {
                deleteDir($file);
            } else {
                unlink($file);
            }
        }
        rmdir($dirPath);
    }
	
	createZipFile($path);
	deleteDir($path);
	$zipname="files/".$path.".zip";
	  
	  //backup code starts from here
	  
	  
require_once 'lib/src/Google_Client.php';
require_once 'lib/src/contrib/Google_DriveService.php';
$client = new Google_Client();
$client->setClientId(''); 		//client id
$client->setClientSecret('');   //client secret

$client->setRedirectUri('https://rtdownloader.000webhostapp.com/backup.php');
$client->setScopes(array('https://www.googleapis.com/auth/drive'));
if (isset($_GET['code'])) {
    $_SESSION['accessToken1'] = $client->authenticate($_GET['code']);
    //header('location:'.$url);exit;
} elseif (!isset($_SESSION['accessToken1'])) {
    $client->authenticate();
}
 
 
 
 $files= array();
$dir = dir('files');
while ($file = $dir->read()) {
    if ($file != '.' && $file != '..') {
        $files[] = $file;
    }
}
$dir->close();
if (!empty($_POST)) {
    $client->setAccessToken($_SESSION['accessToken1']);
    $service = new Google_DriveService($client);
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $file = new Google_DriveFile();
    foreach ($files as $file_name) {
        $file_path = 'files/'.$file_name;
        $mime_type = finfo_file($finfo, $file_path);
        $file->setTitle($file_name);
        $file->setDescription('This is a '.$mime_type.' document');
        $file->setMimeType($mime_type);
        $service->files->insert(
            $file,
            array(
                'data' => file_get_contents($file_path),
                'mimeType' => $mime_type
            )
        );
    }
    finfo_close($finfo);
	
	
	//bacup code end
	
    unlink($zipname);   //delete zip file from server
    header('location:uploadsuccess.php');exit;
}

	
	
	
	
	?>

    <?php	
		
			}
			?>
			 
	 
			 </form>
			 
					
					
					
				</div>
				<div class="col-md-2 sidenav">
                    	<input type="button" class="btn btn-default"  onclick="window.location = 'logout.php'"  value="Log Out">
                </div>
			</div>
			
	
			</div>
			
		</div>
	</section>
	
	<!-- Footer Section Strat -->	

	<section id="footer" class="footer-color">
		<div class="container">
			<div class="row">
				<div class="col-md-12">
					<div class="copyright">
						<p>2018  Made by Manish Mangyani &copy; . (7405301432)</p>
					</div>
				</div>
			</div>
		</div>
	</section>
    

	<!-- Footer section end -->
	
		<script type="text/javascript" src="js/jquery-2.1.3.min.js"></script>
		<!-- Bootstrap JS -->
		<script type="text/javascript" src="js/bootstrap.min.js"></script>
		<script type="text/javascript" src="source/jquery.fancybox.pack.js"></script>
		<script type="text/javascript" src="source/jquery.mousewheel.pack.js"></script>
		<!-- Custom Script -->
		<script type="text/javascript" src="js/scripts.js"></script>
		
	</body>
</html>
