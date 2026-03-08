<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }
    nav{
        width: 100%;
        height: 60px;
        background-color: #ccc;
        display: flex;
        justify-content: space-between;   
    }
    .contenedor-logo{
       background-color: red;
       width: auto;
       display: flex;
       align-items: center;
       gap: 10px;
       padding: 20px;
    }
    .logo img{
        width: 50px;
        height: 50px;
    }

    .contenedor-opciones{
        background-color: green;
        width: 100px;
    }

    .contenedor-usuario{
        background-color: blue;
        width: 100px;
    }
</style>

<body>
    <nav>
        <div class="contenedor-logo">
            <div class="logo">
                <img src="{{asset('assets/img/logo.png')}}" alt="">
            </div>
            <div class="buscador">
                <input type="text">
            </div>
        </div>
        <div class="contenedor-opciones">
            <div>
                <a href=""></a>
            </div>
            <div>
                <a href=""></a>
            </div>
            <div>
                <a href=""></a>
            </div>
            <div>
                <a href=""></a>
            </div>
        </div>
        <div class="contenedor-usuario">
            <div></div>
            <div></div>
            <div></div>
            <div></div>
        </div>
    </nav>
</body>

</html>
