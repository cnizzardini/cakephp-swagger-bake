<?php
$this->assign('title', $title);
?>
<!-- HTML for static distribution bundle build -->
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SWAGGER -->
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
        /* Flash messages */
        .message,.alert {
            padding: 1rem;
            border-width: 1px;
            border-style: solid;
            border-radius: 4px;
            margin-bottom: 2rem;
        }
        .message.error, .alert.alert-danger {
            background: #fcebea;
            color: #cc1f1a;
            border-color: #ef5753;
        }
    </style>
</head>

<body>
<?php
echo $this->Flash->render();
echo $this->fetch('content');
?>
</body>
</html>
