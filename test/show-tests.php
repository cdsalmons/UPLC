<!DOCTYPE html>
<html lang="en">
<head>
	
	<!-- Data Tags -->
	<title>UPLC Test Suite</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	
	<!-- Styles -->
	<style type="text/css">
		
		body {
			margin: 50px;
			color: #124;
			font-size: 14px;
			font-family: "lucida grande", helvetica, verdana, arial, sans-serif;
		}
		
		h1 {
			padding: 3px;
			font-size: 20px;
			font-weight: bold;
			border-bottom: 1px #444 solid;
		}
		
		div {
			padding: 10px 25px;
		}
		
		h2 {
			font-size: 16px;
			font-weight: normal;
		}
		
		a {
			color: #26a;
		}
		
	</style>
	
</head>
<body>
	
	<h1>UPLC Test Suite</h1>
	
	<div id="content">
		<h2>Please select the test you wish to run</h2>
		<ul id="test">
		<?php
			import('files');
			$files = Files()->read_directory('tests');
			foreach ($files as $file) :
				$file = explode('.', $file);
				$file = $file[0];
		?>
			<li><a href="?lib=<?php echo $file; ?>"><?php echo $file; ?></a></li>
		<?php endforeach; ?>
		</ul>
	</div>
	
</body>
</html>
