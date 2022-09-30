<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title><?php echo "$code $title"; ?></title>
	<?php include 'generic.css.php'; ?>
</head>
	<body>
		<div class="interstitial-wrapper">
			<div class="icon" alt="" style=""></div>
			<div class="content">
				<h1><?php echo $title; ?></h1>
				<div class="error-desc">
					<p>
						<span class="uppercase">[<?php echo $code; ?>]</span> <?php echo $body; ?>
					</p>
				</div>
				<div class="row">
					<button type="button" onclick="location.reload()" class="button">Reload</button>
				</div>
			</div>
		</div>
	</body>
</html>
