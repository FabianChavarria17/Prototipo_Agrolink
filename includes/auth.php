<?php
require_once __DIR__ . '/../config/db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function usuarioActual(): ?array {
    if (!isset($_SESSION['usuario_id'])) return null;
    static $cache = null;
    if ($cache !== null) return $cache;
    $stmt = getDB()->prepare('SELECT * FROM usuarios WHERE id = ?');
    $stmt->execute([$_SESSION['usuario_id']]);
    $cache = $stmt->fetch() ?: null;
    return $cache;
}

function requerirLogin(): void {
    if (!usuarioActual()) {
        header('Location: login.php');
        exit;
    }
}

function requerirTipo(string $tipo): void {
    requerirLogin();
    $u = usuarioActual();
    if ($u['tipo'] !== $tipo) {
        header('Location: index.php');
        exit;
    }
}

function setFlash(string $tipo, string $mensaje): void {
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensaje' => $mensaje];
}

function tomarFlash(): ?array {
    if (!isset($_SESSION['flash'])) return null;
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $f;
}

function crearNotificacion(int $usuarioId, string $mensaje): void {
    $stmt = getDB()->prepare('INSERT INTO notificaciones (usuario_id, mensaje) VALUES (?, ?)');
    $stmt->execute([$usuarioId, $mensaje]);
}

function contarNotificacionesNoLeidas(int $usuarioId): int {
    $stmt = getDB()->prepare('SELECT COUNT(*) FROM notificaciones WHERE usuario_id = ? AND leido = 0');
    $stmt->execute([$usuarioId]);
    return (int) $stmt->fetchColumn();
}

function formatoColones(float $monto): string {
    return '₡' . number_format($monto, 0, ',', '.');
}
