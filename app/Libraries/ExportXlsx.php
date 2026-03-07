<?php
namespace App\Libraries;

class ExportXlsx {
    private static function colLetter($index) {
        $index = intval($index);
        $letters = '';
        while ($index >= 0) {
            $letters = chr($index % 26 + 65) . $letters;
            $index = intdiv($index, 26) - 1;
        }
        return $letters;
    }
    public static function generate(array $rows, string $sheetName = 'Sheet1') {
        $colsCount = count($rows > 0 ? $rows[0] : []);
        $rowsCount = count($rows);
        $shared = [];
        $sharedMap = [];
        $count = 0;
        foreach ($rows as $row) {
            foreach ($row as $cell) {
                $val = (string)$cell;
                if (!array_key_exists($val, $sharedMap)) {
                    $sharedMap[$val] = count($shared);
                    $shared[] = $val;
                }
                $count++;
            }
        }
        $sst = '<?xml version="1.0" encoding="UTF-8"?>'
             . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . $count . '" uniqueCount="' . count($shared) . '">';
        foreach ($shared as $s) {
            $sEsc = htmlspecialchars($s, ENT_XML1 | ENT_COMPAT, 'UTF-8');
            $sst .= '<si><t>' . $sEsc . '</t></si>';
        }
        $sst .= '</sst>';
        $lastColLetter = self::colLetter(max($colsCount-1, 0));
        $dimensionRef = 'A1:' . $lastColLetter . max($rowsCount, 1);
        $sheet = '<?xml version="1.0" encoding="UTF-8"?>'
               . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
               . '<sheetPr/><dimension ref="' . $dimensionRef . '"/>'
               . '<sheetViews><sheetView workbookViewId="0"/></sheetViews>'
               . '<sheetFormatPr defaultRowHeight="15"/>'
               . '<sheetData>';
        $rowNum = 1;
        foreach ($rows as $row) {
            $sheet .= '<row r="' . $rowNum . '">';
            $col = 0;
            foreach ($row as $cell) {
                $val = (string)$cell;
                $sIdx = $sharedMap[$val];
                $ref = self::colLetter($col) . $rowNum;
                $sheet .= '<c r="' . $ref . '" t="s"><v>' . $sIdx . '</v></c>';
                $col++;
            }
            $sheet .= '</row>';
            $rowNum++;
        }
        $sheet .= '</sheetData></worksheet>';
        $contentTypes = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml" ContentType="application/xml"/>'
            . '<Override PartName="/docProps/app.xml" ContentType="application/vnd.openxmlformats-officedocument.extended-properties+xml"/>'
            . '<Override PartName="/docProps/core.xml" ContentType="application/vnd.openxmlformats-package.core-properties+xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '</Types>';
        $rels = '<?xml version="1.0" encoding="UTF-8"?>'
              . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
              . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
              . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/package/2006/relationships/metadata/core-properties" Target="docProps/core.xml"/>'
              . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/extended-properties" Target="docProps/app.xml"/>'
              . '</Relationships>';
        $workbook = '<?xml version="1.0" encoding="UTF-8"?>'
                  . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                  . '<workbookPr date1904="false"/>'
                  . '<bookViews><workbookView/></bookViews>'
                  . '<sheets>'
                  . '<sheet name="' . htmlspecialchars($sheetName, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '" sheetId="1" r:id="rId1"/>'
                  . '</sheets>'
                  . '</workbook>';
        $workbookRels = '<?xml version="1.0" encoding="UTF-8"?>'
                      . '<Relationships xmlns="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
                      . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
                      . '<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>'
                      . '<Relationship Id="rId3" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
                      . '</Relationships>';
        $styles = '<?xml version="1.0" encoding="UTF-8"?>'
                . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<fonts count="1"><font/></fonts>'
                . '<fills count="1"><fill/></fills>'
                . '<borders count="1"><border/></borders>'
                . '<cellStyleXfs count="1"><xf/></cellStyleXfs>'
                . '<cellXfs count="1"><xf xfId="0"/></cellXfs>'
                . '</styleSheet>';
        $coreProps = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                   . '<cp:coreProperties xmlns:cp="http://schemas.openxmlformats.org/package/2006/metadata/core-properties" '
                   . 'xmlns:dc="http://purl.org/dc/elements/1.1/" xmlns:dcterms="http://purl.org/dc/terms/" '
                   . 'xmlns:dcmitype="http://purl.org/dc/dcmitype/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">'
                   . '<dc:title>' . htmlspecialchars($sheetName, ENT_XML1 | ENT_COMPAT, 'UTF-8') . '</dc:title>'
                   . '<dc:creator>ExportXlsx</dc:creator>'
                   . '<cp:revision>1</cp:revision>'
                   . '</cp:coreProperties>';
        $appProps = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                  . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties" '
                  . 'xmlns:vt="http://schemas.openxmlformats.org/officeDocument/2006/docPropsVTypes">'
                  . '<Application>ExportXlsx</Application>'
                  . '</Properties>';
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($tmp, \ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rels);
        $zip->addFromString('docProps/core.xml', $coreProps);
        $zip->addFromString('docProps/app.xml', $appProps);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRels);
        $zip->addFromString('xl/worksheets/sheet1.xml', $sheet);
        $zip->addFromString('xl/sharedStrings.xml', $sst);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->close();
        $bin = file_get_contents($tmp);
        @unlink($tmp);
        return $bin;
    }
}
