<?php
declare(strict_types=1);
require_once __DIR__ . '/app.php';

function manager_env(): array {
    static $values;
    if (is_array($values)) return $values;
    $values=[];
    $path=__DIR__ . '/../manager/.env';
    if (!is_file($path) || !is_readable($path)) return $values;
    foreach (file($path,FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line=trim($line);
        if ($line==='' || $line[0]==='#' || !str_contains($line,'=')) continue;
        [$key,$value]=explode('=',$line,2);
        $key=trim($key);
        if (!preg_match('/^MANAGER_[A-Z0-9_]+$/',$key)) continue;
        $value=trim($value);
        if (strlen($value)>=2 && (($value[0]==='"' && str_ends_with($value,'"')) || ($value[0]==="'" && str_ends_with($value,"'")))) $value=substr($value,1,-1);
        $values[$key]=$value;
    }
    return $values;
}
function manager_config(string $key,string $default=''): string {
    $environment=getenv($key);
    if ($environment!==false) return $environment;
    return manager_env()[$key] ?? $default;
}
function manager_admin_email(): string {
    $email=strtolower(trim(manager_config('MANAGER_ADMIN_EMAIL')));
    if ($email==='') $email=strtolower(trim(app_config('NILETECK_ADMIN_EMAIL')));
    return filter_var($email,FILTER_VALIDATE_EMAIL) ? $email : '';
}
function manager_demo_enabled(): bool {
    return filter_var(manager_config('MANAGER_DEMO_ENABLED','true'),FILTER_VALIDATE_BOOLEAN);
}
