<?php
$this->assign('title', $title);
?>
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $this->fetch('title'); ?></title>
    <link rel="stylesheet" type="text/css" href="/swagger_bake/swagger-ui.css" >
    <link rel="icon" type="image/png" href="/swagger_bake/favicon-32x32.png" sizes="32x32" />
    <link rel="icon" type="image/png" href="/swagger_bake/favicon-16x16.png" sizes="16x16" />
    <style>
        html
        {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }

        *,
        *:before,
        *:after
        {
            box-sizing: inherit;
        }

        body
        {
            margin:0;
            background: #fafafa;
        }
    </style>
</head>

<body>
<?php
echo $this->fetch('content');
?>
</body>
</html>
