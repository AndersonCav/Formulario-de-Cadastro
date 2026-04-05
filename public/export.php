<?php
require_once __DIR__.'/../config/env.php';
require_once __DIR__.'/../config/session.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once __DIR__.'/../config/database.php';
require_once __DIR__.'/../src/Csrf.php';
require_once __DIR__.'/../src/Logger.php';

AppLogger::setLogDir(__DIR__.'/../storage/logs');

$is_admin = (int) ($_SESSION['is_admin'] ?? 0) === 1;

// Admin only para exportação completa
if (!$is_admin) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!Csrf::verify()) {
        $_SESSION['__flash'] = ['type' => 'error', 'message' => 'Sessão expirada. Tente novamente.'];
        header('Location: export.php');
        exit;
    }

    $data_inicio = trim($_POST['data_inicio'] ?? '');
    $data_fim = trim($_POST['data_fim'] ?? '');

    $sql = 'SELECT * FROM forms WHERE 1=1';
    $params = [];

    if ($data_inicio !== '') {
        $sql .= ' AND data_hora >= :data_inicio';
        $params[':data_inicio'] = $data_inicio;
    }
    if ($data_fim !== '') {
        $sql .= ' AND data_hora <= :data_fim';
        $params[':data_fim'] = $data_fim . ' 23:59:59';
    }

    $sql .= ' ORDER BY data_hora ASC';

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $rows = $stmt->fetchAll();
    } catch (PDOException $e) {
        AppLogger::error('Erro na exportação', ['error' => $e->getMessage()]);
        $_SESSION['__flash'] = ['type' => 'error', 'message' => 'Erro ao preparar exportação.'];
        header('Location: export.php');
        exit;
    }

    // XLSX output
    require_once __DIR__.'/../vendor/autoload.php';
    use PhpOffice\PhpSpreadsheet\Spreadsheet;
    use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    function formatPhoneNumber($phoneNumber) {
        $clean = preg_replace('/\D/', '', $phoneNumber);
        if (strlen($clean) === 10) return preg_replace('/^(\d{2})(\d{4})(\d{4})$/', '($1) $2-$3', $clean);
        if (strlen($clean) === 11) return preg_replace('/^(\d{2})(\d{5})(\d{4})$/', '($1) $2-$3', $clean);
        return $phoneNumber;
    }

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $headers = ['Nome','Telefone','Celular','Email','Profissão','Nº Registro','Conselho','Evento','Cidade','Estado','Data e Hora','Representante'];
    $cols = ['A','B','C','D','E','F','G','H','I','J','K','L'];

    foreach ($headers as $i => $h) {
        $sheet->setCellValue($cols[$i].'1', $h);
    }

    $row = 2;
    foreach ($rows as $data) {
        $sheet->setCellValue('A'.$row, $data['nome']);
        $sheet->setCellValue('B'.$row, formatPhoneNumber($data['telefone']));
        $sheet->setCellValue('C'.$row, formatPhoneNumber($data['celular']));
        $sheet->setCellValue('D'.$row, $data['email'] ?? '');
        $sheet->setCellValue('E'.$row, $data['profissao']);
        $sheet->setCellValueExplicit('F'.$row, $data['numero_registro'], \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('G'.$row, $data['conselho']);
        $sheet->setCellValue('H'.$row, $data['evento'] ?? '');
        $sheet->setCellValue('I'.$row, $data['cidade']);
        $sheet->setCellValue('J'.$row, $data['estado']);
        $sheet->setCellValue('K'.$row, date('d/m/Y H:i', strtotime($data['data_hora'])));
        $sheet->setCellValue('L'.$row, $data['representante']);
        $row++;
    }

    $filename = 'dados_' . date('d-m-Y_H-i-s') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header("Content-Disposition: attachment;filename=\"{$filename}\"");
    header('Cache-Control: max-age=0');

    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
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
</head>
<body>
<?php include __DIR__.'/../views/partials/navbar.php'; ?>
<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header text-center bg-light">
                    <h2 class="mb-0">Exportar para Excel</h2>
                </div>
                <div class="card-body">
                    <form method="post" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
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
