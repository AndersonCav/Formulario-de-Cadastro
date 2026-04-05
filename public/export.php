<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';
require_once __DIR__.'/../src/helpers.php';
require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

// Verifica dependência antes de tentar usar PhpSpreadsheet
$autoload = __DIR__.'/../vendor/autoload.php';
if (!file_exists($autoload)) {
    require_once __DIR__.'/../src/Logger.php';
    AppLogger::setLogDir(__DIR__.'/../storage/logs');
    AppLogger::error('Vendor/autoload.php não encontrado');
    require_once __DIR__.'/../src/Flash.php';
    $_SESSION['__flash'] = ['type' => 'error', 'message' => 'Dependências não instaladas. Execute: composer install'];
    header('Location: export.php');
    exit;
}
require_once $autoload;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify()) {
        header('Location: export.php'); exit;
    }

    if (!is_admin()) {
        header('Location: dashboard.php'); exit;
    }

    $dInicio = trim($_POST['data_inicio'] ?? '');
    $dFim = trim($_POST['data_fim'] ?? '');
    $sql = 'SELECT * FROM forms WHERE 1=1';
    $params = [];

    if ($dInicio !== '') { $sql .= ' AND data_hora >= ?'; $params[] = $dInicio; }
    if ($dFim !== '') { $sql .= ' AND data_hora <= ?'; $params[] = $dFim.' 23:59:59'; }
    $sql .= ' ORDER BY data_hora ASC';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        AppLogger::error('Erro na exportação', ['error' => $e->getMessage()]);
        Flash::set('error', 'Erro ao preparar exportação.');
        header('Location: export.php'); exit;
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $headers = ['Nome','Telefone','Celular','Email','Profissão','Nº Registro','Conselho','Evento','Cidade','Estado','Data/Hora','Representante'];
    foreach ($headers as $i => $h) $sheet->setCellValue(chr(65+$i).'1', $h);

    $r = 2;
    foreach ($rows as $row) {
        $cleanPhone = function($p) {
            $n = preg_replace('/\D/', '', $p);
            if (strlen($n) === 10) return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $n);
            if (strlen($n) === 11) return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $n);
            return $p;
        };
        $sheet->setCellValue('A'.$r, $row['nome']);
        $sheet->setCellValue('B'.$r, $cleanPhone($row['telefone']));
        $sheet->setCellValue('C'.$r, $cleanPhone($row['celular']));
        $sheet->setCellValue('D'.$r, $row['email'] ?? '');
        $sheet->setCellValue('E'.$r, $row['profissao']);
        $sheet->setCellValueExplicit('F'.$r, $row['numero_registro'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('G'.$r, $row['conselho']);
        $sheet->setCellValue('H'.$r, $row['evento'] ?? '');
        $sheet->setCellValue('I'.$r, $row['cidade']);
        $sheet->setCellValue('J'.$r, $row['estado']);
        $sheet->setCellValue('K'.$r, date('d/m/Y H:i', strtotime($row['data_hora'])));
        $sheet->setCellValue('L'.$r, $row['representante']);
        $r++;
    }

    $filename = 'dados_'.date('d-m-Y_H-i-s').'.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');
    (new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet))->save('php://output');
    exit;
}

// GET - mostra o formulário
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exportar | Cadastro System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="./css/navbar.css">
    <style>.card-header{background-color:#60b5ba;color:#fff;text-align:center;}</style>
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h2 class="mb-0">Exportar para Excel</h2></div>
                <div class="card-body">
                    <?php require_once __DIR__.'/../src/Flash.php'; Flash::renderIfPresent(); ?>
                    <form method="post">
                        <?php echo Csrf::field(); ?>
                        <div class="mb-3">
                            <label for="data_inicio" class="form-label">Data de Início</label>
                            <input type="date" class="form-control" id="data_inicio" name="data_inicio">
                        </div>
                        <div class="mb-3">
                            <label for="data_fim" class="form-label">Data de Término</label>
                            <input type="date" class="form-control" id="data_fim" name="data_fim">
                        </div>
                        <button type="submit" class="btn btn-primary">Exportar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
