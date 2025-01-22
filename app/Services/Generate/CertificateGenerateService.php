<?php

namespace App\Services\Generate;

use App\Models\Certificates\NonDestructiveTest;
use App\Models\References\{
    FluidityLimit,
    StrengthLimit,
    HardnessLimit,
    MassFraction,
};
use App\Models\Certificates\Certificate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\RichText\RichText;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Writer\Xls;

class CertificateGenerateService
{
    /**
     * @var Certificate
     */
    protected Certificate $certificate;

    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
        $this->prepareService = new CertificateGeneratePrepareService($certificate);
        if ($certificate->standard && !$certificate->standard->is_show_absorbed_energy) {
            $this->isFillEnergy = false;
        }
    }

    /**
     * @return Xls
     */
    public function generateCertificate(): Xls
    {
        $certificateName = sprintf(
            "СЕРТИФИКАТ КАЧЕСТВА (ПАСПОРТ) № %s от %s",
            $this->certificate->number,
            $this->certificate->created_at,
        );

        $spreadsheet = $this->getTemplate();
        $this->defaultFont = $spreadsheet->getDefaultStyle()
            ->getFont()
            ->setSize(12)
            ->setName('Times New Roman');

        $sheet1 = $spreadsheet->getSheet(0);
        $sheet2 = $spreadsheet->getSheet(1);
        $sheet3 = $spreadsheet->getSheet(2);

        $this->fillSheet1($sheet1, $certificateName);
        $this->fillSheet2($sheet2, $certificateName);
        $this->fillSheet3($sheet3, $certificateName);

        $spreadsheet->setActiveSheetIndex(0);

        return new Xls($spreadsheet);
    }

    /**
     * @return Spreadsheet
     * @throws \Exception
     */
    private function getTemplate(): Spreadsheet
    {
        $filePath = $this->isFillEnergy
            ? __DIR__ . '/../../../cert_templates/common.xlsx'
            : __DIR__ . '/../../../cert_templates/cert_templates/tu01.xlsx';

        if (!file_exists($filePath)) {
            throw new \Exception('Не найден шаблон сертификата', 404);
        }

        $reader = IOFactory::createReaderForFile($filePath);
        return $reader->load($filePath);
    }

    /**
     * @param Worksheet $sheet
     * @param string $certificateName
     * @return void
     */
    private function fillSheet1(Worksheet $sheet, string $certificateName): void
    {
        $arrayRefs = [];

        $sheet->setCellValue('A6', $certificateName);
        $currentRow = $this->fillCommonParams($sheet, $arrayRefs);
        $currentRow = $this->fillNonDestructiveTests($sheet, $currentRow, $arrayRefs);

        if ($arrayRefs) {
            $sheet->mergeCells("A$currentRow:O$currentRow");
            $sheet->getStyle("A$currentRow")->getAlignment()->setWrapText(true);
            $sheet->setCellValue("A$currentRow", $this->generateRefCell($arrayRefs));
            $sheet->getRowDimension($currentRow)->setRowHeight(5 * (count($arrayRefs) + 1), 'mm');
            $sheet->getStyle("A$currentRow:O$currentRow")
                ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("A$currentRow:O$currentRow")
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        }
    }

    /**
     * @param Worksheet $sheet
     * @param string $certificateName
     * @return void
     */
    private function fillSheet2(Worksheet $sheet, string $certificateName): void
    {
        $lastColumn = $this->isFillEnergy ? 'U' : 'Q';

        $fluidityLimit = FluidityLimit::findByCertificate($this->certificate);
        $strengthLimit = StrengthLimit::findByCertificate($this->certificate);
        $hardnessLimit = HardnessLimit::findByCertificate($this->certificate);
        $minRelativeExtension = $this->prepareService->getMinRelativeExtension();
        $minAbsorbedEnergy = $this->prepareService->getMinAbsorbedEnergy();
        $minAverageAbsorbedEnergy = $this->prepareService->getMinAbsorbedEnergy(true);

        if ($fluidityLimit) {
            $sheet->setCellValue(
                'H10',
                $this->generateRichText(
                    "не менее "
                    . number_format($fluidityLimit->value_min)
                    . "\n\nне более "
                    . number_format($fluidityLimit->value_max),
                    true
                )
            );
        }
        if ($strengthLimit) {
            $sheet->setCellValue(
                'J10',
                $this->generateRichText("не менее "
                    . number_format($strengthLimit->value), true)
            );
        }
        if ($minRelativeExtension) {
            $sheet->setCellValue(
                'L10',
                $this->generateRichText("не менее "
                    . number_format($minRelativeExtension), true)
            );
        }
        if ($hardnessLimit) {
            $sheet->setCellValue(
                'N11',
                $this->generateRichText("не более "
                    . number_format($hardnessLimit->value), true)
            );
        }
        if ($minAbsorbedEnergy && $this->isFillEnergy) {
            $sheet->setCellValue(
                'Q11',
                $this->generateRichText("не менее "
                    . number_format($minAbsorbedEnergy), true)
            );
        }
        if ($minAverageAbsorbedEnergy && $this->isFillEnergy) {
            $sheet->setCellValue(
                'T11',
                $this->generateRichText("не менее "
                    . number_format($minAverageAbsorbedEnergy), true)
            );
        }
        $sheet->setCellValue($lastColumn . "10", $this->generateRichText("не менее " . Certificate::GRAIN_MAX, true));
        $sheet->setCellValue('A7', $certificateName);

        $arrayRefs = [];
        $rollsRef = 1;
        if ($this->certificate->rolls_note) {
            foreach ($this->certificate->rolls as $roll) {
                if ($roll->is_use_note) {
                    $arrayRefs[] = $this->certificate->rolls_note;
                    break;
                }
            }
        }

        $key = 0;
        $currentRow = 12;
        $firstBlockRow = $currentRow;
        foreach ($this->certificate->rolls() as $roll) {
            $key++;
            if ($this->certificate->rolls_note && $roll->is_use_note) {
                $sheet->setCellValue("A$currentRow", $this->generateRichText($key, false, $rollsRef));
            } else {
                $sheet->setCellValue("A$currentRow", $this->generateRichText($key . '  '));
            }
            $sheet->setCellValue("B$currentRow", $roll->number);
            $sheet->setCellValue("C$currentRow", $roll->meld->number);
            $sheet->setCellValue("D$currentRow", $roll->wallThickness ? $roll->wallThickness->name : '');
            $sheet->setCellValue(
                "E$currentRow",
                $roll->length ? number_format($roll->length, 2) : ''
            );
            if ($roll->locationSeams) {
                $ref = null;
                if ($roll->location_seams_note) {
                    $arrayRefs[] = $roll->location_seams_note;
                    $ref = count($arrayRefs);
                }
                $sheet->setCellValue(
                    "F$currentRow",
                    $this->generateRichText(number_format($roll->locationSeams, 2), false, $ref)
                );
            } else {
                $sheet->setCellValue("F$currentRow", '');
            }
            $sheet->mergeCells("F$currentRow:G$currentRow");
            //$sheet->setCellValue("H$currentRow", $roll->is_fill_testing ? ($roll->fluidity ?? '') : '');
            if ($roll->fluidity) {
                $ref = null;
                if ($roll->fluidity_note) {
                    $arrayRefs[] = $roll->fluidity_note;
                    $ref = count($arrayRefs);
                }
                $sheet->setCellValue(
                    "H$currentRow",
                    $this->generateRichText(number_format($roll->fluidity, 2), false, $ref)
                );
            } else {
                $sheet->setCellValue("H$currentRow", '');
            }
            $sheet->mergeCells("H$currentRow:I$currentRow");
            //$sheet->setCellValue("J$currentRow", $roll->is_fill_testing ? ($roll->strength ?? '') : '');
            $sheet->setCellValue("J$currentRow", $roll->strength ?? '');
            $sheet->mergeCells("J$currentRow:K$currentRow");

            if ($roll->relative_extension) {
                $ref = null;
                if ($roll->relative_extension_note) {
                    $arrayRefs[] = $roll->relative_extension_note;
                    $ref = count($arrayRefs);
                }
                $sheet->setCellValue(
                    "L$currentRow",
                    $this->generateRichText(number_format($roll->relative_extension, 2), false, $ref)
                );
            } else {
                $sheet->setCellValue("L$currentRow", '');
            }
            $sheet->mergeCells("L$currentRow:M$currentRow");
            if ($roll->hardness_om) {
                $sheet->setCellValue(
                    "N$currentRow",
                    ($roll->is_exact_hardness_om ? '' : '< ') . number_format($roll->hardness_om, 2)
                );
            }
            if ($roll->hardness_ssh) {
                $sheet->setCellValue(
                    "O$currentRow",
                    ($roll->is_exact_hardness_ssh ? '' : '< ') . number_format($roll->hardness_ssh, 2)
                );
            }
            if ($roll->hardness_ztv) {
                $sheet->setCellValue(
                    "P$currentRow",
                    ($roll->is_exact_hardness_ztv ? '' : '< ') . number_format($roll->hardness_ztv, 2)
                );
            }

            if (/*$roll->is_fill_testing*/ $this->isFillEnergy) {
                $countEnergy = 0;
                $totalEnergy = 0;
                if ($roll->absorbed_energy_1) {
                    $sheet->setCellValue(
                        "Q$currentRow",
                        number_format($roll->absorbed_energy_1, 2)
                    );
                    $totalEnergy += $roll->absorbed_energy_1;
                    $countEnergy += 1;
                }
                if ($roll->absorbed_energy_2) {
                    $sheet->setCellValue(
                        "R$currentRow",
                        number_format($roll->absorbed_energy_2, 2)
                    );
                    $totalEnergy += $roll->absorbed_energy_2;
                    $countEnergy += 1;
                }
                if ($roll->absorbed_energy_3) {
                    $sheet->setCellValue(
                        "S$currentRow",
                        number_format($roll->absorbed_energy_3, 2)
                    );
                    $totalEnergy += $roll->absorbed_energy_3;
                    $countEnergy += 1;
                }
                if ($countEnergy) {
                    $sheet->setCellValue(
                        "T$currentRow",
                        number_format(round($totalEnergy / $countEnergy, 2), 2)
                    );
                }
            }

            $sheet->setCellValue("$lastColumn$currentRow", $roll->grain_size ?? '');

            $currentRow++;
        }
        $lastBlockRow = $currentRow - 1;

        $sheet->getStyle("A$firstBlockRow:$lastColumn$lastBlockRow")
            ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$firstBlockRow:$lastColumn$lastBlockRow")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        if ($arrayRefs) {
            $sheet->mergeCells("A$currentRow:$lastColumn$currentRow");
            $sheet->getStyle("A$currentRow")->getAlignment()->setWrapText(true);
            $sheet->setCellValue("A$currentRow", $this->generateRefCell($arrayRefs));
            $sheet->getRowDimension($currentRow)->setRowHeight(5 * (count($arrayRefs) + 1), 'mm');
            $sheet->getStyle("A$currentRow:$lastColumn$currentRow")
                ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("A$currentRow:$lastColumn$currentRow")
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
        }
    }

    /**
     * @param Worksheet $sheet
     * @param string $certificateName
     * @return void
     */
    private function fillSheet3(Worksheet $sheet, string $certificateName): void
    {
        $arrayRefs = [];

        $sheet->setCellValue('A7', $certificateName);

        $sheet->setCellValue('G9', $this->generateRichText("Сэкв, %", true));

        $massFraction = MassFraction::findByCertificate($this->certificate);
        if ($massFraction) {
            $sheet->setCellValue(
                'B11',
                $this->generateRichText(
                    "не более " . number_format($massFraction->carbon),
                    true
                )
            );
            $sheet->setCellValue(
                'C11',
                $this->generateRichText(
                    "не более " . number_format($massFraction->manganese),
                    true
                )
            );
            $sheet->setCellValue(
                'D11',
                $this->generateRichText(
                    "не более " . number_format($massFraction->silicon),
                    true
                )
            );
            $sheet->setCellValue(
                'E11',
                $this->generateRichText(
                    "не более " . number_format($massFraction->sulfur),
                    true
                )
            );
            $sheet->setCellValue(
                'F11',
                $this->generateRichText(
                    "не более " . number_format($massFraction->phosphorus),
                    true
                )
            );
            $sheet->getStyle("B11:F11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->setCellValue(
                'H11',
                $this->generateRichText(
                    "не более " . number_format(Certificate::DIRTY_MAX),
                    true
                )
            );
            $sheet->getStyle("H11:L11")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        $currentRow = 12;
        $firstRow = $currentRow;
        foreach ($this->certificate->melds as $meld) {
            $sheet->setCellValue("A$currentRow", $meld->number ?? '');
            $sheet->setCellValue(
                "B$currentRow",
                $meld->chemical_c ? number_format($meld->chemical_c, 3) : ''
            );
            $sheet->setCellValue(
                "C$currentRow",
                $meld->chemical_mn ? number_format($meld->chemical_mn, 3) : ''
            );
            $sheet->setCellValue(
                "D$currentRow",
                $meld->chemical_si ? number_format($meld->chemical_si, 3) : ''
            );
            $sheet->setCellValue(
                "E$currentRow",
                $meld->chemical_s ? number_format($meld->chemical_s, 3) : ''
            );
            $sheet->setCellValue(
                "F$currentRow",
                $meld->chemical_p ? number_format($meld->chemical_p, 3) : ''
            );
            if ($meld->sekv) {
                $ref = null;
                if ($meld->sekv_note) {
                    $arrayRefs[] = $meld->sekv_note;
                    $ref = count($arrayRefs);
                }
                $sheet->setCellValue(
                    "G$currentRow",
                    $this->generateRichText(number_format($meld->sekv, 2), false, $ref)
                );
            } else {
                $sheet->setCellValue("G$currentRow", '');
            }
            $sheet->setCellValue(
                "H$currentRow",
                $meld->dirty_type_a ? number_format($meld->dirty_type_a, 2) : ''
            );
            $sheet->setCellValue(
                "I$currentRow",
                $meld->dirty_type_b ? number_format($meld->dirty_type_b, 2) : ''
            );
            $sheet->setCellValue(
                "J$currentRow",
                $meld->dirty_type_c ? number_format($meld->dirty_type_c, 2) : ''
            );
            $sheet->setCellValue(
                "K$currentRow",
                $meld->dirty_type_d ? number_format($meld->dirty_type_d, 2) : ''
            );
            $sheet->setCellValue(
                "L$currentRow",
                $meld->dirty_type_ds ? number_format($meld->dirty_type_ds, 2) : ''
            );
            $currentRow++;
        }
        $lastRow = $currentRow - 1;
        $sheet->getStyle("A$firstRow:L$lastRow")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$firstRow:L$lastRow")->getBorders()
            ->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

        if ($arrayRefs) {
            $sheet->mergeCells("A$currentRow:L$currentRow");
            $sheet->getStyle("A$currentRow")->getAlignment()->setWrapText(true);
            $sheet->setCellValue("A$currentRow", $this->generateRefCell($arrayRefs));
            $sheet->getRowDimension($currentRow)->setRowHeight(5 * (count($arrayRefs) + 1), 'mm');
            $sheet->getStyle("A$currentRow:L$currentRow")
                ->getAlignment()->setVertical(Alignment::VERTICAL_TOP);
            $sheet->getStyle("A$currentRow:L$currentRow")
                ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THIN);
            $currentRow++;
        }

        $sheet->mergeCells("A$currentRow:L$currentRow");
        $currentRow++;

        $currentRow = $this->fillCylinder($sheet, $currentRow);
        $sheet->mergeCells("A$currentRow:L$currentRow");
        $currentRow++;

        $sheet->setCellValue("A$currentRow", "Примечания:");
        $sheet->mergeCells("A$currentRow:L$currentRow");
        $currentRow++;
        foreach ($this->certificate->notes as $key => $note) {
            $sheet->setCellValue("A$currentRow", ($key + 1) . '. ' . ($note->text ?? ''));
            $sheet->mergeCells("A$currentRow:L$currentRow");
            $currentRow++;
        }
        $currentRow++;
        $sheet->setCellValue("A$currentRow", "Составил:");
        $currentRow += 2;
        foreach ($this->certificate->signatures as $signature) {
            $sheet->setCellValue("A$currentRow", $signature->position);
            $sheet->setCellValue("G$currentRow", $signature->name);
            $currentRow += 3;
        }
    }

    /**
     * @param Worksheet $sheet
     * @param int $currentRow
     * @return int
     */
    private function fillCylinder(Worksheet $sheet, int $currentRow): int
    {
        $sheet->setCellValue("A$currentRow", $this->generateRichText("Сведения об упаковке ГНКТ ", true));
        $sheet->mergeCells("A$currentRow:L$currentRow");
        $sheet->getStyle("A$currentRow:L$currentRow")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$currentRow:L$currentRow")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THICK);
        $currentRow++;

        $firstBlockRow = $currentRow;
        foreach ($this->prepareService->getCylinderData() as $item) {
            $sheet->setCellValue("A$currentRow", $item['title']);
            $sheet->mergeCells("A$currentRow:E$currentRow");
            $sheet->setCellValue("F$currentRow", $item['value']);
            $sheet->mergeCells("F$currentRow:L$currentRow");
            $currentRow++;
        }
        if ($firstBlockRow != $currentRow) {
            $lastBlockRow = $currentRow - 1;
            $sheet->getStyle("A$firstBlockRow:L$lastBlockRow")
                ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
            $sheet->getStyle("F$firstBlockRow:L$lastBlockRow")
                ->getAlignment()
                ->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        return $currentRow;
    }

    /**
     * @param Worksheet $sheet
     * @param $arrayRefs
     * @return int
     */
    private function fillCommonParams(Worksheet $sheet, &$arrayRefs): int
    {
        $currentRow = 8;
        $firstRow = $currentRow;
        foreach ($this->prepareService->getCommonData() as $item) {
            $sheet->mergeCells("A$currentRow:E$currentRow");
            $sheet->mergeCells("F$currentRow:O$currentRow");
            $sheet->getStyle("A$currentRow:O$currentRow")->getAlignment()->setWrapText(true);

            $insertRef = null;
            if ($item['ref'] ?? null) {
                $arrayRefs[] = $item['ref'];
                $insertRef = count($arrayRefs);
            }

            $sheet->setCellValue(
                "A$currentRow",
                $this->generateRichText($item['title'], true, $insertRef, $item['unit'] ?? null)
            );
            $sheet->setCellValue("F$currentRow", $item['value'] ?? '');

            $currentRow++;
        }
        $lastRow = $currentRow - 1;

        $sheet->getStyle("F$firstRow:O$lastRow")
            ->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        $sheet->getStyle("A$firstRow:O$lastRow")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$firstRow:E$lastRow")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle("F$firstRow:O$lastRow")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);

        return $currentRow;
    }

    /**
     * @param string $message
     * @param bool $isBold
     * @param int|null $ref
     * @param string|null $unit
     * @return RichText
     */
    private function generateRichText(
        string  $message,
        bool    $isBold = false,
        ?int    $ref = null,
        ?string $unit = null
    ): RichText
    {
        $rText = new RichText();
        $rText->createTextRun($message)->setFont(clone $this->defaultFont)->getFont()->setBold($isBold);

        if (!is_null($ref)) {
            $rText->createTextRun("$ref)")
                ->setFont(clone $this->defaultFont)->getFont()->setSuperscript(true);
        }
        if (!is_null($unit)) {
            $rText->createTextRun(", $unit")->setFont(clone $this->defaultFont)->getFont()->setBold($isBold);
        }
        return $rText;
    }

    /**
     * @param Worksheet $sheet
     * @param int $currentRow
     * @param $arrayRefs
     * @return int
     */
    private function fillNonDestructiveTests(Worksheet $sheet, int $currentRow, &$arrayRefs): int
    {
        $tests = $this->certificate->getNonDestructiveTestsAsArray();
        $sheet->mergeCells("A$currentRow:O$currentRow");
        $sheet->setCellValue(
            "A$currentRow",
            $this->generateRichText("Неразрушающий контроль", true)
        );
        $sheet->getStyle("A$currentRow")->getAlignment()
            ->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("A$currentRow:O$currentRow")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $currentRow++;

        $firstBlockRow = $currentRow;
        $sheet->mergeCells("A$currentRow:E$currentRow");
        $sheet->setCellValue(
            "A$currentRow",
            $this->generateRichText("Объект контроля", true)
        );
        $sheet->mergeCells("F$currentRow:H$currentRow");
        $sheet->setCellValue(
            "F$currentRow",
            $this->generateRichText("Метод контроля", true)
        );
        $sheet->mergeCells("I$currentRow:K$currentRow");
        $sheet->setCellValue(
            "I$currentRow",
            $this->generateRichText("НД на контроль", true)
        );
        $sheet->mergeCells("L$currentRow:O$currentRow");
        $sheet->setCellValue(
            "L$currentRow",
            $this->generateRichText("Результат контроля", true)
        );
        $sheet->getStyle("A$currentRow")->getAlignment()
            ->setWrapText(true)->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $currentRow++;

        foreach (['cross_seams_roll_ends', 'longitudinal_seams', 'base_metal',
                     'circular_corner_seam'] as $method) {
            if (array_key_exists($method, $tests)) {
                if (is_array($tests[$method])) {
                    $firstMethodRow = $currentRow;
                    foreach ($tests[$method] as $test) {
                        $insertRef = null;
                        if ($test->note) {
                            $arrayRefs[] = $test->note;
                            $insertRef = count($arrayRefs);
                        }
                        $this->insertNonDestructiveTest($sheet, $currentRow, $test, $insertRef);
                        $currentRow++;
                    }
                    $lastMethodRow = $currentRow - 1;
                    $sheet->mergeCells("A$firstMethodRow:E$lastMethodRow");
                } else {
                    $test = $tests[$method];
                    if ($test) {
                        $insertRef = null;
                        if ($test->note) {
                            $arrayRefs[] = $test->note;
                            $insertRef = count($arrayRefs);
                        }
                        $this->insertNonDestructiveTest($sheet, $currentRow, $test, $insertRef);
                        $currentRow++;
                    }
                }
            }
        }
        $lastBlockRow = $currentRow - 1;

        $sheet->getStyle("A$firstBlockRow:O$lastBlockRow")
            ->getAlignment()
            ->setWrapText(true)
            ->setHorizontal(Alignment::HORIZONTAL_CENTER)
            ->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle("A$firstBlockRow:O$lastBlockRow")
            ->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);
        $sheet->getStyle("A$firstBlockRow:E$lastBlockRow")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);
        $sheet->getStyle("F$firstBlockRow:O$lastBlockRow")
            ->getBorders()->getOutline()->setBorderStyle(Border::BORDER_THICK);

        return $currentRow;
    }

    /**
     * @param Worksheet $sheet
     * @param int $row
     * @param NonDestructiveTest $test
     * @param int|null $ref
     * @return void
     */
    private function insertNonDestructiveTest(Worksheet $sheet, int $row, NonDestructiveTest $test, ?int $ref): void
    {
        $sheet->mergeCells("A$row:E$row");
        $sheet->mergeCells("F$row:H$row");
        $sheet->mergeCells("I$row:K$row");
        $sheet->mergeCells("L$row:O$row");

        $sheet->setCellValue(
            "A$row",
            $this->generateRichText($test->controlObject->name, true)
        );
        $sheet->setCellValue(
            "F$row",
            $this->generateRichText($test->controlMethod->name ?? '', false, $ref)
        );
        $sheet->setCellValue(
            "I$row",
            $this->generateRichText($test->ndControlMethod->name ?? '')
        );
        $sheet->setCellValue(
            "L$row",
            $this->generateRichText($test->controlResult->text ?? '')
        );
    }

    /**
     * @param array $refs
     * @return RichText
     */
    private function generateRefCell(array $refs): RichText
    {
        $rText = new RichText();
        foreach ($refs as $key => $value) {
            $refs[$key] = ($key + 1) . ') ' . $value;
        }

        $rText->createTextRun(implode("\n", $refs))
            ->setFont(clone $this->defaultFont)
            ->getFont()
            ->setSuperscript(true);
    }
}
