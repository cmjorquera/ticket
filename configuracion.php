<?php
    session_start();
    require_once 'class/conexion.php';
    require_once 'class/funciones.php';
    require_once 'class/personas.php';

    
    $funciones          = new Funciones();
    $personas           = new Persona();

    $idUsuarioSession   = htmlspecialchars($_SESSION['id']);

    $idPagActual        = "9";  // Página de bitácora
    
    // *************** INSTANCIA DE LAS FUNCIONES
    $usuario                       = $personas->datosUsuario($idUsuarioSession );
    // $usuarios                   = $funciones->listarUsuarios();
    // $tiposDispositivos          = $funciones->listarTiposDispositivos();
    // $listarOtrosDispositivos    = $funciones->listarOtrosDispositivos();
    // $listarTodosDispositivosqr  = $funciones->listarTodosDispositivosqr();


?>

<!DOCTYPE html>
<html lang="es">
<meta charset="UTF-8">

<head>
    <?php $funciones->header(); ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intro.js/minified/introjs.min.css">    <link rel="stylesheet" href="css/estilo.css">
    <link rel="stylesheet" href="css/bitacora.css"> <!-- PROPIO DE ESTA PAGINA -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="js/comunes.js"></script> <!-- FUNCIONES COMUNES -->
    <script src="js/equipos.js"></script> <!-- FUNCIONES DE COMPUTADORES -->
    <script src="js/dispositivos.js"></script> <!-- FUNCIONES DE DISPOSITIVOS -->

</head>

<style>
:root{
  --bg: #f6f7fb;
  --card: #ffffff;
  --ink: #0f172a;
  --muted:#6b7280;
  --shadow: 0 8px 24px rgba(0,0,0,.06);
  --primary:#6c7cff;
  --primary-ink:#2f3dd7;
  --red:#ef4444;
  --yellow:#f59e0b;
  --green:#10b981;
  --blue:#3b82f6;
  --violet:#8b5cf6;
  --chip:#eef2ff;
  --chip-ink:#4f46e5;
  --radius:18px;
}

*{box-sizing:border-box}
html,body{height:100%}
body{
  margin:0;
  font-family:Inter,system-ui,-apple-system,Segoe UI,Roboto,Arial;
  color:var(--ink);
  background:var(--bg);
}

/* Layout */
.app{
  max-width:1200px;
  margin:32px auto;
  padding:0 20px;
  display:grid;
  grid-template-columns: 1.6fr .9fr;
  gap:24px;
}
@media (max-width: 980px){
  .app{ grid-template-columns: 1fr; }
}

/* Top filters / progress */
.toolbar{
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:16px;
  display:flex;
  gap:12px;
  align-items:center;
  justify-content:space-between;
}
.toolbar .group{
  display:flex; gap:10px; flex-wrap:wrap;
}
.chip{
  background:#f3f4f6;
  border:1px solid #e5e7eb;
  padding:10px 12px;
  border-radius:12px;
  font-size:14px;
  color:#374151;
  cursor:default;
}
.progress{
  display:flex; align-items:center; gap:10px;
}
.progress small{ color:var(--muted) }
.bar{
  height:8px; width:160px; background:#eef2ff; border-radius:999px; overflow:hidden;
}
.bar > i{ display:block; height:100%; width:45%; background:var(--primary); }

/* Timeline */
.timeline{
  position:relative;
  padding-left:26px;
  display:flex;
  flex-direction:column;
  gap:16px;
}
.timeline::before{
  content:"";
  position:absolute; left:11px; top:0; bottom:0;
  width:2px; background:#e9e9f3;
}
.time-sep{
  position:relative;
  margin:14px 0 4px;
  font-weight:600; color:var(--muted);
}
.time-dot{
  position:absolute; left:-20px; top:50%;
  transform:translate(-50%,-50%);
  width:12px; height:12px; border-radius:50%;
  background:#c7d2fe; border:3px solid var(--card);
  box-shadow:0 0 0 2px #c7d2fe;
}

/* Reminder card */
.card{
  position:relative;
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:16px 16px 14px 16px;
  display:flex; flex-direction:column; gap:10px;
}
.card::before{
  content:"";
  position:absolute; left:-21px; top:18px;
  width:14px; height:14px; border-radius:50%;
  background:var(--primary);
  border:3px solid var(--card);
  box-shadow:0 0 0 2px #c7d2fe;
}
.card h4{ margin:0; font-size:16px }
.meta{
  display:flex; align-items:center;
  gap:12px; flex-wrap:wrap; color:var(--muted); font-size:14px;
}
.meta .tag{ 
  padding:6px 10px; border-radius:999px; font-weight:600; 
  font-size:12px;
}
.tag.estado{ color:#fff; }
.tag.atrasado{ background:var(--red); }
.tag.hoy{ background:var(--blue); }
.tag.pendiente{ background:#9ca3af; color:#111827; }
.tag.completado{ background:var(--green); }
.tag.prio{
  background:var(--chip);
  color:var(--chip-ink);
  border:1px solid #e0e7ff;
}
.kv{ display:flex; align-items:center; gap:6px; }
.kv i{ font-style:normal; color:var(--primary-ink); }

.footer-actions{
  display:flex; align-items:center; gap:10px; margin-top:2px;
}
.btn{
  border:none; outline:none; cursor:pointer;
  padding:10px 12px; border-radius:12px;
  background:#f3f4f6; color:#374151; font-weight:600;
}
.btn.primary{ background:var(--primary); color:#fff; }
.btn.ghost{ background:transparent; border:1px dashed #e5e7eb; }

/* Sidebar */
.sidebar{
  display:flex; flex-direction:column; gap:16px;
}
.panel{
  background:var(--card);
  border-radius:var(--radius);
  box-shadow:var(--shadow);
  padding:16px;
}
.panel h3{ margin:0 0 12px; font-size:18px; }
.calendar{
  display:grid;
  grid-template-columns: repeat(7, 1fr);
  gap:6px;
  user-select:none;
}
.calendar .hd{
  text-align:center; font-weight:600; color:var(--muted); font-size:12px;
}
.calendar .d{
  text-align:center; padding:8px 0; border-radius:10px; font-weight:600;
  color:#374151; background:#f8fafc;
}
.calendar .d.is-today{ background:#e0e7ff; color:var(--primary-ink); }
.quick{
  display:flex; flex-direction:column; gap:8px;
}
.quick .chip{ width:100%; text-align:center; }

/* Mini list bottom */
.board{
  display:grid; grid-template-columns: repeat(3, 1fr); gap:12px;
}
@media (max-width: 740px){
  .board{ grid-template-columns:1fr; }
}
.card.small{ padding:14px; }
.card.small h5{ margin:0 0 6px; font-size:15px; }
.card.small .meta{ gap:8px; }
</style>
<body id="page-top">
    <div id="wrapper">
        <?php $funciones->menuLateral($idUsuarioSession, $idPagActual); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>
                    <?php $funciones->cabezera(); ?>
                </nav>
                <div class="container-fluid">
                    <section class="section profile">
                        <div class="row">
                            <div class="col-xl-4">
                                <div class="card">
 <div class="app">

    <!-- Columna izquierda -->
    <div>
      <div class="toolbar">
        <div class="group">
          <div class="chip">Filtro por estado</div>
          <div class="chip">Rango de fechas</div>
        </div>
        <div class="progress">
          <small>Completados 3/7</small>
          <div class="bar"><i></i></div>
        </div>
      </div>

      <div class="timeline" style="margin-top:16px">
        <div class="time-sep"><span class="time-dot"></span>Hoy</div>

        <article class="card">
          <h4>Llamar a proveedor</h4>
          <div class="meta">
            <span class="tag estado atrasado">Atrasado</span>
            <span class="kv"><i>📅</i> 13 de abril, 10:00</span>
            <span class="tag prio">Alta</span>
            <span class="kv">👤 Miguel</span>
            <span class="kv">💼 Negocios</span>
          </div>
          <div class="footer-actions">
            <button class="btn primary">Marcar hecho</button>
            <button class="btn">Posponer</button>
            <button class="btn ghost">⋯</button>
          </div>
        </article>

        <article class="card">
          <h4>Enviar informe</h4>
          <div class="meta">
            <span class="tag estado hoy">Hoy</span>
            <span class="kv"><i>⏰</i> 14:00</span>
            <span class="tag prio">Media</span>
            <span class="kv">👤 Emily</span>
            <span class="kv">🗂️ Trabajo</span>
          </div>
          <div class="footer-actions">
            <button class="btn primary">Marcar hecho</button>
            <button class="btn">Posponer</button>
            <button class="btn ghost">⋯</button>
          </div>
        </article>

        <div class="time-sep"><span class="time-dot"></span>Mañana</div>

        <article class="card">
          <h4>Pedir cita médica</h4>
          <div class="meta">
            <span class="tag estado pendiente">Pendiente</span>
            <span class="kv"><i>📅</i> 15 de abril, 09:00</span>
            <span class="tag prio">Baja</span>
            <span class="kv">👤 Kristin</span>
            <span class="kv">🏷️ Personal</span>
          </div>
          <div class="footer-actions">
            <button class="btn primary">Marcar hecho</button>
            <button class="btn">Posponer</button>
            <button class="btn ghost">⋯</button>
          </div>
        </article>

        <div class="time-sep"><span class="time-dot"></span>Próxima semana</div>

        <article class="card">
          <h4>Revisar presupuesto</h4>
          <div class="meta">
            <span class="tag estado completado">Completado</span>
            <span class="kv"><i>📅</i> 22 de abril, 14:00</span>
            <span class="tag prio">Baja</span>
            <span class="kv">👤 Robert</span>
            <span class="kv">💰 Finanzas</span>
          </div>
          <div class="footer-actions">
            <button class="btn">Deshacer</button>
            <button class="btn ghost">⋯</button>
          </div>
        </article>

        <!-- mini tablero inferior -->
        <div class="board">
          <article class="card small">
            <h5>LLamar a proveedor</h5>
            <div class="meta"><span class="kv">📅 Hoy</span><span class="tag prio">Media</span></div>
            <div class="footer-actions"><button class="btn">Marcar hecho</button></div>
          </article>
          <article class="card small">
            <h5>Enviar informe</h5>
            <div class="meta"><span class="kv">⏰ 14:00</span><span class="kv">🗂️ Trabajo</span></div>
            <div class="footer-actions"><button class="btn">Marcar hecho</button></div>
          </article>
          <article class="card small">
            <h5>Redirigir presupuesto</h5>
            <div class="meta"><span class="kv">📅 Próxima semana</span></div>
            <div class="footer-actions"><button class="btn">Marcar hecho</button></div>
          </article>
        </div>
      </div>
    </div>

    <!-- Sidebar derecha -->
    <aside class="sidebar">
      <section class="panel">
        <h3>Recordatorios</h3>
        <div class="calendar" aria-label="Calendario (UI estática)">
          <div class="hd">L</div><div class="hd">M</div><div class="hd">M</div>
          <div class="hd">J</div><div class="hd">V</div><div class="hd">S</div><div class="hd">D</div>
          <!-- semana 1 -->
          <div class="d">1</div><div class="d">2</div><div class="d">3</div><div class="d">4</div>
          <div class="d">5</div><div class="d">6</div><div class="d">7</div>
          <!-- semana 2 -->
          <div class="d">8</div><div class="d">9</div><div class="d">10</div><div class="d">11</div>
          <div class="d is-today">12</div><div class="d">13</div><div class="d">14</div>
          <!-- semana 3 -->
          <div class="d">15</div><div class="d">16</div><div class="d">17</div><div class="d">18</div>
          <div class="d">19</div><div class="d">20</div><div class="d">21</div>
          <!-- semana 4 -->
          <div class="d">22</div><div class="d">23</div><div class="d">24</div><div class="d">25</div>
          <div class="d">26</div><div class="d">27</div><div class="d">28</div>
          <!-- semana 5 -->
          <div class="d">29</div><div class="d">30</div><div class="d">31</div><div class="d"> </div>
          <div class="d"> </div><div class="d"> </div><div class="d"> </div>
        </div>
      </section>

      <section class="panel">
        <div class="quick">
          <div class="chip">Hoy</div>
          <div class="chip">Mañana</div>
          <div class="chip">Próxima semana</div>
        </div>
      </section>

      <section class="panel">
        <h3>Ajustes rápidos</h3>
        <div class="quick">
          <div class="chip">Repetir</div>
          <div class="chip">Notificaciones</div>
          <div class="chip">Solo atrasados</div>
        </div>
      </section>
    </aside>
  </div>                                </div>
                            </div>
                        </div>
                    </section>
                </div>
                <a class="scroll-to-top rounded" href="#page-top">
                    <i class="fas fa-angle-up"></i>
                </a>
            </div>
            <?php $funciones->footer(); ?>
        </div>
    </div>
</body>
<?php $funciones->script(); ?>
<?php include("modal_salir.php"); ?>


</html>