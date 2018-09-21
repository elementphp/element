<html>
    <head>
        <title>{block "title"}element{/block}</title>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <style type="text/css">
        .container {
            padding-top:50px;
        }
        h3 {
            color: gray;
        }
    </style>
</head>
<body>

    <div class="container">
        <div class="row">
            <div class="page-header">
                <h1>Welcome to element</h1>
                <h3><small>{$welcomeMessage}</small></h3>
            </div>
        </div>

        <div class="row">
            <p><br /><a href="https://elementphp.com/documentation" class="btn btn-success">View the docs &rarr;</a></p>
        </div>
    </div>
</body>


</html>