<!DOCTYPE html>
<?php $startFiles = Config::buildStartFiles(); ?>
<html <?php if ($startFiles['manifest']): ?> manifest="<?php echo $startFiles['manifest']; ?>"<?php endif; ?>>
<head>
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script type="text/javascript" src="<?php echo $startFiles['project']; ?>"></script>
    <script type="text/javascript" src="<?php echo $startFiles['auth']; ?>"></script>
    <script type="text/javascript" src="<?php echo $startFiles['templates']; ?>"></script>
    <script type="text/javascript" src="<?php echo $startFiles['dataProvider']; ?>"></script>
    <script type="text/javascript" src="<?php echo $startFiles['loader']; ?>"></script>
</head>
<body></body>
</html>