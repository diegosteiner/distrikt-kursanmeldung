<?php
include_once './config.php';
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept');

if(!empty($_REQUEST)) {
  $errors = [];

  if(checkParams() && checkSecurity()) {
    $name = preg_replace("([^\w\d])", '-', $_REQUEST['name']);
    $abteilung = $_REQUEST['abteilung'];
    $kurs = $_REQUEST['kurs'];
    $target_dir = "/uploads/$abteilung/$name";
    is_dir(__DIR__ . $target_dir) || mkdir(__DIR__ . $target_dir, 0755, true);
    moveFiles(__DIR__ . $target_dir) || $errors[]= 'Angehängte Dateien konnten nicht verarbeitet werden';
    empty($errors) && sendMail($name, $abteilung, $kurs);
  } else {
    $errors[]= 'Angaben unvollständig oder falsch';
  }
}

function checkParams()
{
  global $config;

  return isset($_REQUEST['name']) &&
    array_key_exists($_REQUEST['abteilung'], $config['abteilungen']) &&
    in_array(strtolower($_REQUEST['kurs']), ['pio', 'futura', 'basis', 'aufbau']);
}

function checkSecurity()
{
  return in_array(strtolower($_REQUEST['security-question']), ['krawatte', 'grawatte', 'foulard']);
}

function moveFiles($target_dir)
{
  if(empty($_FILES)) return false; 

  foreach ($_FILES as $uploadName => $uploadFile) {
    $target = "$target_dir/" . $uploadName . '_' . $uploadFile['name'];
    move_uploaded_file($uploadFile['tmp_name'], $target);
  }
  return true;
}

function sendMail($name, $abteilung, $kurs)
{
  global $config;

  $to = $config['abteilungen'][$abteilung];
  $url = $config['base_url'] . "/uploads/$kurs/$name";

  if (!empty($to)) 
    mail($to, 
      "Anmeldeunterlagen von $name ($kurs)", 
      "Die Anmeldeunterlagen von $name von $abteilung für den Kurs '$kurs' wurden hochgeladen:\n\n$url"
    );
  return true;
}

?>

<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Distrikt St. Georg</title>
  <link rel="stylesheet" href="style.css" />
</head>

<body>
  <div class="container">
    <div class="row">
      <h1>Kursanmeldungen Distrikt St. Georg</h1>
    </div>
    <?php if(!isset($errors) || !empty($errors)): ?>
      <form enctype="multipart/form-data" class="row" action="." method="POST">
        <?php if(!empty($errors)): ?>
          <p class="errors"><?= implode(', ', $errors); ?></p>
        <?php endif; ?>
        <div class="form-group">
          <label for="name">Pfadiname<span class="required">*</span></label>
          <input type="text" name="name" maxlength="100" minlength="2" id="name" required="required" aria-required="true" />
        </div>
        <div class="form-group">
          <label for="abteilung">Abteilung<span class="required">*</span></label>
          <select name="abteilung" id="abteilung" required="required" aria-required="true">
            <option value=""></option>

            <optgroup label="Glattal">
              <option value="morea">Morea</option>
              <option value="agua">Agua</option>
              <option value="luzi">St. Luzi</option>
              <option value="felix">St. Felix</option>
              <option value="jakob">St. Jakob</option>
              <option value="winki">Winkelried</option>
              <option value="lepanto">Lepanto</option>
            </optgroup>
            <optgroup label="Limmat">
              <option value="smn">St. Mauritius Nansen</option>
              <option value="laupen">Laupen</option>
              <option value="ulrich">St. Ulrich</option>
              <option value="sa">Säuliamt</option>
              <option value="murten">Murten</option>
              <option value="sempach">Sempach</option>
              <option value="uro">Uro</option>
            </optgroup>
            <optgroup label="Uto">
              <option value="friesen">Friesen</option>
              <option value="at">Attinghausen</option>
              <option value="zb">Züriberg</option>
            </optgroup>
          </select>
        </div>
        <div class="form-group">
          <label>Kurs<span class="required">*</span></label>
          <label><input required="required" type="radio" name="kurs" value="pio" id="kurs-pio" /> Piokurs</label>
          <label><input required="required" type="radio" name="kurs" value="futura" id="kurs-futura" /> Futurakurs</label>
          <label><input required="required" type="radio" name="kurs" value="basis" id="kurs-basis" /> Basiskurs</label>
          <label><input required="required" type="radio" name="kurs" value="aufbau" id="kurs-aufbau" /> Aufbaukurs</label>
        </div>
        <div id="anmeldung-group" class="form-group">
          <label for="anmeldung-file">Unterschriebene Kursanmeldung als PDF<span class="required">*</span></label>
          <input type="file" name="anmeldung-file" multiple="false" accept=".pdf,application/pdf" id="anmeldung-file" />
        </div>
        <div id="over-18-group" class="form-group hidden">
          <span class="or">oder</span>
          <label for="over-18">
          <input type="checkbox" id="over-18" name="over-18" /> Ich bin schon über 18</label>
        </div>
        <div id="nothelfer-group" class="hidden" class="form-group">
          <label for="nothelfer-file">Nothilfekurs Bestätigung als PDF<span class="required">*</span></label>
          <input type="file" name="nothelfer-file" multiple="false" accept=".pdf,application/pdf" id="nothelfer-file" />
        </div>
        <div class="form-group">
          <label for="security-question">Was hat ein Pfadi um den Hals?<span class="required">*</span></label>
          <input type="text" name="security-question" id="security-question" required="required" minlength="5" />
        </div>
        <div class="form-group">
          <button type="submit">Submit</button>
        </div>
      </form>
    <?php else: ?>
      <p>Danke!</p>
    <?php endif; ?>
  </div>
  <script>
    const form = document.forms[0]
    const nhkFormGroup = document.getElementById('nothelfer-group')
    const nhkInput = nhkFormGroup.querySelector("input[type='file']")
    const anmeldungFormGroup = document.getElementById('anmeldung-group')
    const anmeldungInput = anmeldungFormGroup.querySelector("input[type='file']")
    const over18FormGroup = document.getElementById('over-18-group')
    
    for(let radio of document.querySelectorAll("input[name='kurs']")) {
      radio.addEventListener('change', (e) => {
        const isBasis = e.target.value == 'basis' && e.target.checked
        const isAufbau = e.target.value == 'aufbau' && e.target.checked
        nhkFormGroup.classList.toggle('hidden', !isBasis) 
        nhkInput.required = isBasis ? 'required' : ''
        over18FormGroup.classList.toggle('hidden', !(isBasis || isAufbau)) 
      })
    }

    document.querySelector("input[name='over-18']").addEventListener('change', (e) => {
      const checked = e.target.checked
      anmeldungInput.required = checked ? '' : 'required'
      anmeldungFormGroup.querySelector('.required').classList.toggle('hidden', checked)
    })
  </script>
</body>

</html>
