<?php
if (!isset($page_title)) {
    $page_title = app_config('app.name', '10 Days Weekly Challenge');
}
?><!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($page_title) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lexend:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: { sans: ['Lexend', 'ui-sans-serif', 'system-ui'] },
                    colors: {
                        molten: '#E74B05',
                        pumpkin: '#F26E10',
                        indigo_bloom: '#683FB7',
                        brandy: '#722806'
                    }
                }
            }
        }
    </script>
</head>
<body class="min-h-screen bg-orange-50 text-zinc-900 font-sans">
