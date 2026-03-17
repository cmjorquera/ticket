<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: Arial, sans-serif;
        }

    body {
        background-image: url('img/login_prueba.jpg');
        background-size: cover;
        background-position: center;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .login-container {
        display: flex;
        background-color: white;
        border-radius: 10px;
        box-shadow: 0px 4px 20px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        width: 800px;
        height: 400px;
    }

    .login-left {
        background-image: url('img/login.jpg');
        background-size: cover;
        background-position: center;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
        color: white;
        padding: 20px;
    }

    .login-left h1 {
        font-size: 2.5rem;
    }

    .login-left p {
        font-size: 1.2rem;
    }

    .login-right {
        flex: 1;
        padding: 50px;
        display: flex;
        flex-direction: column;
        justify-content: center;
    }

    .profile-pic {
        text-align: center;
        margin-bottom: 20px;
    }

    .profile-pic img {
        border-radius: 50%;
        width: 80px;
        height: 80px;
    }

    .form-group {
        margin-bottom: 15px;
    }

    .form-group label {
        display: block;
        font-size: 0.9rem;
        color: #555;
    }

    .form-group input {
        width: 100%;
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 5px;
        margin-top: 5px;
        font-size: 1rem;
    }

    .login-btn {
        background-color: #004080;
        color: white;
        padding: 10px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        width: 100%;
        font-size: 1rem;
    }

    .login-btn:hover {
        background-color: #003060;
    }

    .links {
        margin-top: 20px;
        display: flex;
        justify-content: space-between;
        font-size: 0.9rem;
    }

    .links a {
        color: #004080;
        text-decoration: none;
    }

    .links a:hover {
        text-decoration: underline;
    }

</style>
<body>
    <div class="login-container">
        <div class="login-left">
            <h1>SISTEMA TICKET</h1>
            <p>Seduc </p>
        </div>
        <div class="login-right">
            <div class="profile-pic">
                <img src="img/undraw_profile_1.svg" alt="Profile Picture">
            </div>
            <form>
                <div class="form-group">
                    <label for="email">Correo</label>
                    <input type="email" id="email" placeholder="Email">
                </div>
                <div class="form-group">
                    <label for="password">Clave</label>
                    <input type="password" id="password" placeholder="Password">
                </div>
                <button type="submit" class="login-btn">Entrar</button>
                <div class="links">
                    <a href="#">Necesita una cuenta? Regístrese</a>
                    <a href="#">¿Olvidaste tu contraseña?</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>