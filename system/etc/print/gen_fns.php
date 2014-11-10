<?php

include_once "fpdf/fpdf.php";
define('FONTFILE', "fpdf/font/times.ttf");
    
//$DTT = $diplomType; 

    
function mb_ucfirst($word) {
  return mb_strtoupper(mb_substr($word, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr(mb_convert_case($word, MB_CASE_LOWER, 'UTF-8'), 1, mb_strlen($word), 'UTF-8');
}

/**
 * Делает первый символ строки строчным, остальные - без изменения
 * @param type $text
 * @return type
 */
function mb_lofirst($text) {
  $firstLetter = mb_strtolower(mb_substr($text, 0, 1, "UTF-8"), "UTF-8");
  $string = mb_substr($text, 1, mb_strlen($text, "UTF-8") - 1, "UTF-8");
  return $firstLetter . $string;
}

function weeks($weeks) {
  settype($weeks, "integer");

  $last = $weeks % 10;

  if ($weeks >= 5 && $weeks <= 20)
    return "недель";
  if ($last == 1)
    return "неделя";
  if ($last >= 2 && $last <= 4)
    return "недели";
  //if ($last == 0 || $last == 5)
  return "недель";
}

function hours($hours) {
  settype($hours, "integer");

  $last = $hours % 10;

  if ($hours >= 5 && $hours <= 20)
    return "часов";
  if ($last == 1)
    return "час";
  if ($last >= 2 && $last <= 4)
    return "часа";
  //if ($last == 0 || $last == 5)
  return "часов";
}

function years($hours) {
  settype($hours, "integer");

  $last = $hours % 10;

  if ($hours >= 5 && $hours <= 20)
    return "лет";
  if ($last == 1)
    return "год";
  if ($last >= 2 && $last <= 4)
    return "года";
  //if ($last == 0 || $last == 5)
  return "лет";
}

function months($hours) {
  settype($hours, "integer");

  $last = $hours % 10;

  if ($hours >= 5 && $hours <= 20)
    return "месяцев";
  if ($last == 1)
    return "месяц";
  if ($last >= 2 && $last <= 4)
    return "месяца";
  //if ($last == 0 || $last == 5)
  return "месяцев";
}

/**
 * Заменяет примыкающие к словам кавычки вида " на « и » 
 */
function fixQuotes($string) {
  $string = " " . trim($string) . " ";
  $patterns[] = "/ \"(.)/iu";
  $patterns[] = "/(.)\" /iu";
  $patterns[] = "/(.)\",/iu";
  $replacements[] = " «\${1}";
  $replacements[] = "\${1}» ";
  $replacements[] = "\${1}»,";
  return trim(preg_replace($patterns, $replacements, $string));
}

function textBounds($text, $fontSize, $fontFile = "system/etc/print/fpdf/font/times.ttf") {
  $rect = imagettfbbox($fontSize, 0, $fontFile, $text);
  $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
  $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
  $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
  $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

  $TimesNewRomanHeights = array(
      '6' => 10, '7' => 12, '8' => 14, '9' => 15, '10' => 15,
      '11' => 17, '12' => 19, '13' => 19, '14' => 21, '15' => 22,
      '16' => 23, '17' => 26, '18' => 27, '19' => 29, '20' => 31,
      '21' => 33, '22' => 33
  );

  if (strpos($fontFile, "times") !== false)
    $height = $TimesNewRomanHeights[$fontSize];
  else
    $height = $maxY - $minY;

  return array(
      "w" => $maxX - $minX,
      "h" => $height
  );
}

function wpx2mm($px, $fontSize) {
  settype($fontSize, "integer");
  settype($px, "integer");

  $coef = array(
      11 => 3.84,
      10 => 3.74,
      9 => 3.85,
      8 => 3.8,
      7 => 3.79,
      6 => 3.97
  );

  if ($fontSize > 11 || $fontSize < 6)
    $k = 3.8;
  else
    $k = $coef[$fontSize];

  return ceil($px / $k);
}

function stringToArrayByWidth($text, $fontSize, $maxWidthINmm) {
  $text = trim(fixQuotes($text));

  $parts = explode(" ", $text);
  for ($i = count($parts); $i > 0; $i--) {
    $words = array_slice($parts, 0, $i);
    $line = implode(" ", $words);
    $bounds = textBounds($line, $fontSize);
    $w = wpx2mm($bounds['w'], $fontSize);
    if ($w <= $maxWidthINmm) {
      $res = array();
      $res[] = $line;

      if ($i == count($parts))
        return $res;

      $otherWords = array_slice($parts, $i);
      $otherLines = implode(" ", $otherWords);
      $end = stringToArrayByWidth($otherLines, $fontSize, $maxWidthINmm);

      $result = array_merge_recursive($res, $end);
      return $result;
    }
  }
}

function p($str) {
  return iconv("utf-8", "cp1251", $str);
}

class DiplomPage extends FPDF {
  /* ===========================================================================
   * 
   *                      P U B L I C   F U N C T I O N S
   * 
   * ======================================================================== */

  private $GX = 0;  //задают смещение в SetXY
  private $GY = 0;

  public function SetGXY($x, $y) {
    $this->GX = $x;
    $this->GY = $y;
  }

  public function SetXY2($x, $y) {
    $this->SetXY($this->GX + $x, $this->GY + $y);
  }

  /**
   * Конвертирует строку из utf-8 в кириллицу windows cp2151
   * @param type $text
   * @return type
   */
  public static function conv($text) {
    return iconv("utf-8", "cp1251", $text);
  }

  /**
   * Заменяет примыкающие к словам кавычки вида " на « и » 
   */
  public static function fixQuotes($string) {

    if (!function_exists("mb_trim")) {

      function mb_trim($string) {
        $string = preg_replace("/(^\s+)|(\s+$)/us", "", $string);

        return $string;
      }

    }

    $string = " " . mb_trim($string) . " ";

    $patterns[] = "/ \"(.)/iu";
    $replacements[] = " «\${1}";
    $patterns[] = "/(.)\" /iu";
    $replacements[] = "\${1}» ";
    $patterns[] = "/(.)\",/iu";
    $replacements[] = "\${1}»,";

    return trim(preg_replace($patterns, $replacements, $string));
  }

  /**
   * 
   * @param type $options описывает каждую колонку для MultilineCell
   * @param type $data массив массивов, внутренние массивы - строчки 
   * 
   * $options = array(array[x y w a fs h],...) //y только у нулевого массива(у 1ой колонки)
   * 
   * 
   */
  public function MultilineColumns($options, $data, $maxheight = false) {
    if (!is_array($options[0]))
      $options = array($options);

    $columns = count($options);
    $rows = count($data);

    $offset = 0;

    for ($i = 0; $i < $rows; $i++) {

      if ($maxheight !== false) {
        if ($offset >= ($maxheight - 5))
          return $i * (-1);
      }

      $max = 0;

      while (count($data[$i]) < $columns)
        $data[$i][] = " ";

      for ($j = 0; $j < $columns; $j++) {
        $sx = $options[$j]['x'];
        $sy = $options[0]['y'] + $offset;
        $sw = $options[$j]['w'];
        $sa = $options[$j]['a'];
        $sfs = $options[$j]['fs'];
        $sh = $options[$j]['h'];



        if (is_array($data[$i]))
          $text = $data[$i][$j];
        else
          $text = $data[$i];

        $height = $this->MultilineCell($sx, $sy, $sw, $text, $sa, $sfs, $sh);
        if ($height > $max)
          $max = $height;
      }
      $offset += $max;
    }
    return $offset;
  }

  /**
   * Разбивает строку в несколько строк по ширине и выводит в pdf
   * @param type $x X-позиция верхнего левого угла блока, в который начинается вывод текста
   * @param type $y Y-позиция верхнего левого угла блока, в который начинается вывод текста
   * @param type $w Ширина блока в миллиметрах
   * @param type $text Строка в utf-8 или уже подготовленный массив строк
   * @param type $align Выравнивание: L (по левому краю), C (по центру), R (по правому краю)
   * @return int Занятая текстом высота в миллиметрах
   */
  public function MultilineCell($x, $y, $w, $text, $align = 'L', $fontSize = 11, $heightMM = 4) {



    // 11 pt
    //каждый элемент массива $strings - строка, которая при наборе шрифтом
    //Times New Roman 11 pt по ширине меньше, чем $w миллиметров
    if (is_array($text))
      $strings = $text;
    else
      $strings = $this->stringToArrayByWidth($text, $fontSize, $w - 2);




    $linesCount = count($strings);
    for ($i = 0; $i < $linesCount; $i++) {
      $this->SetXY2($x - 0.45, $y + $i * $heightMM);

      //костыль для Удовлетворительно
      /* if ($strings[$i] == "Удовлетворительно" && mb_strpos($strings[$i], "Удовлетворительно") !== false) {
        $this->SetFontSize(8);
        $this->Cell($w, $heightMM, self::conv($strings[$i]), 0, 0, $align);
        $this->SetFontSize(11);
        continue;
        } */

      $this->SetFontSize($fontSize);
      $this->Cell($w, $heightMM, self::conv($strings[$i]), 0, 0, $align);
    }

    return $heightMM * $linesCount;
  }

  public function PrintKP($arr, $GX = 0, $GY = 0, $fontsize = 10) {
    $p4c1 = array();
    $p4c2 = array();
    $parr = 0;

    while ($parr < count($arr)) {

      $title = $arr[$parr][0];
      $strings = $this->stringToArrayByWidth($title, $fontsize, 138);
      $stringsCount = count($strings);
      for ($i = 0; $i < $stringsCount; $i++) {
        if ($i == 0) {
          $p4c1[] = $strings[$i];
          $p4c2[] = $arr[$parr][1];
        } else {
          $p4c1[] = $strings[$i];
          $p4c2[] = "";
        }
      }
      $parr++;
    }

    $this->MultilineCell($GX + 13, $GY + 32, 138, $p4c1, 'L', $fontsize);
    $this->MultilineCell($GX + 151, $GY + 32, 55, $p4c2, 'C', $fontsize);
  }

  public function PrintDesciplines($disciplinesArray, $page = 2, $GX = 0, $GY = 0) {
    $arr = &$disciplinesArray;

    $FS = 8;
    $LineHeight = 4;


    $page2 = array();
    $page3 = array();

    $parr = 0;             //указатель на текущую позицию в $arr
    $arrlen = count($arr);  //количество строк для вывода

    $stringsOnPage2 = floor(244 / $LineHeight); // 81; //61
    $stringsOnPage3 = floor(255 / $LineHeight); // 84; //63

    while ($parr < $arrlen) {
      $disciplineTitle = $arr[$parr][0];
      $strings = $this->stringToArrayByWidth($disciplineTitle, $FS, 117);
      $stringsCount = count($strings);
      if ($stringsOnPage2 >= $stringsCount) {
        $stringsOnPage2 -= $stringsCount;
        for ($i = 0; $i < $stringsCount; $i++) {
          if ($i == 0)
            $page2[] = array($strings[$i], $arr[$parr][1], $arr[$parr][2], $arr[$parr][3]);
          else
            $page2[] = array($strings[$i], '', '', '');
        }
        $parr++;
      }
      if ($stringsOnPage2 <= 0)
        break;
    }

    if (!empty($arr[$parr][0]) && ($arr[$parr][0] == "в том числе:" || $arr[$parr][0] == "в том числе аудиторных часов:")) {
      $parr--;
      unset($page2[count($page2) - 1]);
    }

    if (!empty($arr[$parr][0]) && $arr[$parr][0] == "")
      $parr++;

    while ($parr < $arrlen) {
      $disciplineTitle = $arr[$parr][0];
      $strings = $this->stringToArrayByWidth($disciplineTitle, $FS, 117);
      $stringsCount = count($strings);
      if ($stringsOnPage3 >= $stringsCount) {
        $stringsOnPage3 -= $stringsCount;
        for ($i = 0; $i < $stringsCount; $i++) {
          if ($i == 0)
            $page3[] = array($strings[$i], $arr[$parr][1], $arr[$parr][2], $arr[$parr][3]);
          else
            $page3[] = array($strings[$i], '', '', '');
        }
        $parr++;
      }
      if ($stringsOnPage3 <= 0)
        break;
    }

    $p2c1 = array();
    $p2c2 = array();
    $p2c3 = array();
    $p2c4 = array();

    foreach ($page2 as $v) {
      $p2c1[] = $v[0];
      $p2c2[] = $v[1];
      $p2c3[] = $v[2];
      $p2c4[] = $v[3];
    }
    /*
      $debug_array = array("удовлетворит.", "отлично", "удовлетв.", "хорошо", "удовл.", "отлично", "удовлетворительно");
      foreach ($debug_array as $key => $value) {
      $p2c4[$key] = $value;
      }
     */

    $p3c1 = array();
    $p3c2 = array();
    $p3c3 = array();
    $p3c4 = array();

    foreach ($page3 as $v) {
      $p3c1[] = $v[0];
      $p3c2[] = $v[1];
      $p3c3[] = $v[2];
      $p3c4[] = $v[3];
    }


    if ($page == 2) {
      $this->MultilineCell(13.5 - 1, 41.5, 119, $p2c1, 'L', $FS, $LineHeight);
      $this->MultilineCell(132, 41.5, 25, $p2c2, 'C', $FS, $LineHeight);
      $this->MultilineCell(157, 41.5, 25, $p2c3, 'C', $FS, $LineHeight);
      $this->MultilineCell(182 - 1 - 1, 41.5, 25, $p2c4, 'C', $FS, $LineHeight);
    }

    if ($page == 3) {
      $this->MultilineCell(8, 31, 119, $p3c1, 'L', $FS, $LineHeight);
      $this->MultilineCell(126, 31, 25, $p3c2, 'C', $FS, $LineHeight);
      $this->MultilineCell(151, 31, 25, $p3c3, 'C', $FS, $LineHeight);
      $this->MultilineCell(176, 31, 25, $p3c4, 'C', $FS, $LineHeight);
    }

    if ($page == 23) {
      $this->MultilineCell($GX + 13.5 - 1, $GY + 41.5, 119, $p2c1, 'L', $FS, $LineHeight);
      $this->MultilineCell($GX + 132, $GY + 41.5, 25, $p2c2, 'C', $FS, $LineHeight);
      $this->MultilineCell($GX + 157, $GY + 41.5, 25, $p2c3, 'C', $FS, $LineHeight);
      $this->MultilineCell($GX + 182 - 1 - 1, $GY + 41.5, 25, $p2c4, 'C', $FS, $LineHeight);

      $this->MultilineCell($GX + 210 + 8, $GY + 31, 119, $p3c1, 'L', $FS, $LineHeight);
      $this->MultilineCell($GX + 210 + 126, $GY + 31, 25, $p3c2, 'C', $FS, $LineHeight);
      $this->MultilineCell($GX + 210 + 151, $GY + 31, 25, $p3c3, 'C', $FS, $LineHeight);
      $this->MultilineCell($GX + 210 + 176, $GY + 31, 25, $p3c4, 'C', $FS, $LineHeight);
    }
  }

  /* public function PrintDesciplines($disciplinesArray, $page = 2) {
    $arr = &$disciplinesArray;

    $page2 = array();
    $page3 = array();

    $parr = 0;             //указатель на текущую позицию в $arr
    $arrlen = count($arr);  //количество строк для вывода

    $stringsOnPage2 = 61;
    $stringsOnPage3 = 63;

    while ($parr < $arrlen) {
    $disciplineTitle = $arr[$parr][0];
    $strings = $this->stringToArrayByWidth($disciplineTitle, 11, 117);
    $stringsCount = count($strings);
    if ($stringsOnPage2 >= $stringsCount) {
    $stringsOnPage2 -= $stringsCount;
    for ($i = 0; $i < $stringsCount; $i++) {
    if ($i == 0)
    $page2[] = array($strings[$i], $arr[$parr][1], $arr[$parr][2], $arr[$parr][3]);
    else
    $page2[] = array($strings[$i], '', '', '');
    }
    $parr++;
    }
    if ($stringsOnPage2 <= 0)
    break;
    }

    if ($arr[$parr][0] == "в том числе:") {
    $parr--;
    unset($page2[count($page2) - 1]);
    }

    if ($arr[$parr][0] == "")
    $parr++;

    while ($parr < $arrlen) {
    $disciplineTitle = $arr[$parr][0];
    $strings = $this->stringToArrayByWidth($disciplineTitle, 11, 117);
    $stringsCount = count($strings);
    if ($stringsOnPage3 >= $stringsCount) {
    $stringsOnPage3 -= $stringsCount;
    for ($i = 0; $i < $stringsCount; $i++) {
    if ($i == 0)
    $page3[] = array($strings[$i], $arr[$parr][1], $arr[$parr][2], $arr[$parr][3]);
    else
    $page3[] = array($strings[$i], '', '', '');
    }
    $parr++;
    }
    if ($stringsOnPage3 <= 0)
    break;
    }

    $p2c1 = array();
    $p2c2 = array();
    $p2c3 = array();
    $p2c4 = array();

    foreach ($page2 as $v) {
    $p2c1[] = $v[0];
    $p2c2[] = $v[1];
    $p2c3[] = $v[2];
    $p2c4[] = $v[3];
    }

    $p3c1 = array();
    $p3c2 = array();
    $p3c3 = array();
    $p3c4 = array();

    foreach ($page3 as $v) {
    $p3c1[] = $v[0];
    $p3c2[] = $v[1];
    $p3c3[] = $v[2];
    $p3c4[] = $v[3];
    }

    if ($page == 2) {
    $this->MultilineCell(13.5, 41.5, 119, $p2c1);
    $this->MultilineCell(132, 41.5, 25, $p2c2, 'C');
    $this->MultilineCell(157, 41.5, 25, $p2c3, 'C');
    $this->MultilineCell(182, 41.5, 25, $p2c4, 'C');
    }

    if ($page == 3) {
    $this->MultilineCell(8, 31, 119, $p3c1);
    $this->MultilineCell(126, 31, 25, $p3c2, 'C');
    $this->MultilineCell(151, 31, 25, $p3c3, 'C');
    $this->MultilineCell(176, 31, 25, $p3c4, 'C');
    }

    if ($page == 23) {
    $this->MultilineCell(13.5, 41.5, 119, $p2c1);
    $this->MultilineCell(132, 41.5, 25, $p2c2, 'C');
    $this->MultilineCell(157, 41.5, 25, $p2c3, 'C');
    $this->MultilineCell(182, 41.5, 25, $p2c4, 'C');

    $this->MultilineCell(210 + 8, 31, 119, $p3c1);
    $this->MultilineCell(210 + 126, 31, 25, $p3c2, 'C');
    $this->MultilineCell(210 + 151, 31, 25, $p3c3, 'C');
    $this->MultilineCell(210 + 176, 31, 25, $p3c4, 'C');
    }
    }
   */
  /* ===========================================================================
   * 
   *                      P R I V A T E   F U N C T I O N S
   * 
   * ======================================================================== */

  public function stringToArrayByWidth($text, $fontSize, $maxWidthINmm) {
    $text = trim(self::fixQuotes($text));

    $parts = explode(" ", $text);
    for ($i = count($parts); $i > 0; $i--) {
      $words = array_slice($parts, 0, $i);
      $line = implode(" ", $words);
      $bounds = $this->textBounds($line, $fontSize);
      $w = $this->wpx2mm($bounds['w'], $fontSize);
      if ($w <= $maxWidthINmm) {
        $res = array();
        $res[] = $line;

        if ($i == count($parts))
          return $res;

        $otherWords = array_slice($parts, $i);
        $otherLines = implode(" ", $otherWords);
        $end = $this->stringToArrayByWidth($otherLines, $fontSize, $maxWidthINmm);

        if (!is_array($end))
          $end = array($end);
        $result = array_merge_recursive($res, $end);
        return $result;
      }
    }
  }

  private function wpx2mm($px, $fontSize) {
    settype($fontSize, "integer");
    settype($px, "integer");

    $coef = array(
        11 => 3.84,
        10 => 3.74,
        9 => 3.85,
        8 => 3.8,
        7 => 3.79,
        6 => 3.97
    );

    if ($fontSize > 11 || $fontSize < 6)
      $k = 3.8;
    else
      $k = $coef[$fontSize];

    return ceil($px / $k);
  }

  private function textBounds($text, $fontSize, $fontFile = "system/etc/print/fpdf/font/times.ttf") {
    $rect = imagettfbbox($fontSize, 0, $fontFile, $text);
    $minX = min(array($rect[0], $rect[2], $rect[4], $rect[6]));
    $maxX = max(array($rect[0], $rect[2], $rect[4], $rect[6]));
    $minY = min(array($rect[1], $rect[3], $rect[5], $rect[7]));
    $maxY = max(array($rect[1], $rect[3], $rect[5], $rect[7]));

    $TimesNewRomanHeights = array(
        '6' => 10, '7' => 12, '8' => 14, '9' => 15, '10' => 15,
        '11' => 17, '12' => 19, '13' => 19, '14' => 21, '15' => 22,
        '16' => 23, '17' => 26, '18' => 27, '19' => 29, '20' => 31,
        '21' => 33, '22' => 33
    );

    if (strpos($fontFile, "times") !== false)
      $height = $TimesNewRomanHeights[$fontSize];
    else
      $height = $maxY - $minY;

    return array(
        "w" => $maxX - $minX,
        "h" => $height
    );
  }

}
