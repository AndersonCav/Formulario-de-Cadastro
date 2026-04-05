<?php
require_once __DIR__.'/bootstrap.php';
app_bootstrap(['database', 'csrf', 'logger', 'flash']);

// Proteger admin-only desde o início
require_admin();

AppLogger::setLogDir(__DIR__.'/../storage/logs');

// Verifica dependência antes de tentar usar PhpSpreadsheet
$autoload = __DIR__.'/../vendor/autoload.php';
if (!file_exists($autoload)) {
    AppLogger::error('Vendor/autoload.php não encontrado');
    app_flash_redirect('error', 'Dependências não instaladas. Execute: composer install', 'export.php');
}
require_once $autoload;

// Processa POST (exportação)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    app_require_post_csrf('export.php', 'Falha de validação CSRF na exportação');

    $dInicio = trim($_POST['data_inicio'] ?? '');
    $dFim = trim($_POST['data_fim'] ?? '');
    $sql = 'SELECT * FROM forms WHERE 1=1';
    $params = [];

    if ($dInicio !== '') {
        $sql .= ' AND data_hora >= ?';
        $params[] = $dInicio;
    }
    if ($dFim !== '') {
        $sql .= ' AND data_hora <= ?';
        $params[] = $dFim.' 23:59:59';
    }
    $sql .= ' ORDER BY data_hora ASC';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        AppLogger::error('Erro na exportação', ['error' => $e->getMessage()]);
        app_flash_redirect('error', 'Erro ao preparar exportação.', 'export.php');
    }

    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $headers = ['Nome','Telefone','Celular','Email','Profissão','Nº Registro','Conselho','Evento','Cidade','Estado','Data/Hora','Representante'];
    foreach ($headers as $i => $h) {
        $sheet->setCellValue(chr(65 + $i).'1', $h);
    }

    $cleanPhone = static function ($p) {
        $n = preg_replace('/\D/', '', $p);
        if (strlen($n) === 10) {
            return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $n);
        }
        if (strlen($n) === 11) {
            return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $n);
        }

        return $p;
    };

    $r = 2;
    foreach ($rows as $row) {
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

// GET - mostra formulário
$pageTitle = 'Exportar | Cadastro System';
include __DIR__.'/../views/partials/header.php';
include __DIR__.'/../views/partials/navbar.php';
?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header"><h2 class="mb-0">Exportar para Excel</h2></div>
                <div class="card-body">
                    <?php Flash::renderIfPresent(); ?>
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
<?php include __DIR__.'/../views/partials/footer.php'; ?>
