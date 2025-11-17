<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />

  <title>BarberDuke - Barbearia perto de mim em BH | Planos e preços acessíveis</title>

  <meta name="description" content="A melhor barbearia de BH em negrito e com muitos planos, além de valores acessíveis e serviço de excelência. Barbearia moderna e perto de você!" />

  <link rel="stylesheet" href="/barberduke/static/style.css" />
</head>

<body>
  <div class="container">
    <!-- LOGIN -->
    <div class="form-box active" id="login-box">
      <h1>Bem-vindo à BarberDuke!</h1>
      <p class="subtexto">Vamos agendar?</p>
      <form action="/barberduke/api/login.php" method="POST">
        <input name="cpf" type="text" placeholder="Digite seu CPF" required />
        <button type="submit">Entrar</button>
      </form>
      <p class="trocar">Não tem uma conta? 
        <a href="#" id="show-signup">Vamos criar uma!</a>
      </p>
    </div>

    <!-- CADASTRO -->
    <div class="form-box" id="signup-box">
      <h1>Criar Conta</h1>
      <form action="/barberduke/api/cadastrar.php" method="POST">
        <input name="nome" type="text" placeholder="Nome completo" required />
        <input name="cpf" type="text" placeholder="CPF" required />
        <input name="telefone" type="text" placeholder="Telefone" required />
        <button type="submit">Cadastrar</button>
      </form>
      <p class="trocar">Já tem uma conta? 
        <a href="#" id="show-login">Entrar</a>
      </p>
    </div>
  </div>

  <!-- TOASTS -->
  <div id="toast-container">
    <?php
    session_start();
    if (!empty($_SESSION['error'])) {
        echo "<div class='toast error'>{$_SESSION['error']}</div>";
        unset($_SESSION['error']);
    } elseif (!empty($_SESSION['success'])) {
        echo "<div class='toast success'>{$_SESSION['success']}</div>";
        unset($_SESSION['success']);
    }
    ?>
  </div>

  <script>
    const loginBox = document.getElementById('login-box');
    const signupBox = document.getElementById('signup-box');
    const showSignup = document.getElementById('show-signup');
    const showLogin = document.getElementById('show-login');

    showSignup.addEventListener('click', e => {
      e.preventDefault();
      loginBox.classList.remove('active');
      signupBox.classList.add('active');
    });

    showLogin.addEventListener('click', e => {
      e.preventDefault();
      signupBox.classList.remove('active');
      loginBox.classList.add('active');
    });

    document.querySelectorAll('.toast').forEach(toast => {
      setTimeout(() => toast.remove(), 4000);
    });
  </script>
</body>
</html>
