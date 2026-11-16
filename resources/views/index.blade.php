<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Document</title>
    @vite(['resources/js/app.js'])
    <style>
        /* these are necessary styles for vue flow */
        @import '@vue-flow/core/dist/style.css';

        /* this contains the default theme, these are optional styles */
        @import '@vue-flow/core/dist/theme-default.css';
    </style>


</head>
<body>
    <div id="app">
        <in_decrementor> </in_decrementor>
        <color-changing-button></color-changing-button>
        <remove_add-button></remove_add-button>
        <for_vees></for_vees>
        <models></models>
        <template-ref></template-ref>

    </div>

    <a href="{{route('memory_card_game')}}">memory card game</a>
    <a href="{{route('snake_game')}}">snake</a>

</body>
</html>
