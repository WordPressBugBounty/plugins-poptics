<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?php echo esc_html( $subject ); ?></title>
    <style>
        <?php echo esc_html( get_email_block_editor_styles() ); ?>
    </style>
</head>
<body>
    <div class="content">
        <p><?php echo wp_kses_post( $body ); ?></p>
    </div>
</body>
</html>
