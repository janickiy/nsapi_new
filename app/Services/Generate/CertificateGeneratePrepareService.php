<?php

namespace App\Services\Generate;

use App\Models\Certificates\{
    Certificate,
    Roll,
};
use App\Models\References\{
    RelativeExtension,
    AbsorbedEnergyLimit
};

class CertificateGeneratePrepareService
{
    /**
     * @var Certificate
     */
    protected Certificate $certificate;

    /**
     * @param Certificate $certificate
     */
    public function __construct(Certificate $certificate)
    {
        $this->certificate = $certificate;
    }


    /**
     * @return array
     */
    public function getCommonData(): array
    {
        $queryWall = Roll::query()
            ->selectRaw( 'wall_thickness.name as wall,SUM(certificates.roll.length) as length')
            ->join('certificates.meld', 'roll.meld_id', '=', 'meld.id' )
            ->leftJoin('references.wall_thickness', 'roll.wall_thickness_id', '=', 'wall_thickness.id')
            ->where('meld.certificate_id' , $this->certificate->id)
            ->groupBy('wall_thickness.name')
            ->groupBy('wall_thickness.value')
            ->orderBy('wall_thickness.value')
            ->get()
            ->toArray();


        return [
            ['title' => "Вид изделия", 'value' => $this->certificate->product_type ?? ''],
            ['title' => 'Номер изделия', 'value' => $this->certificate->number_tube ?? ''],
            [
                'title' => 'Стандарт на изделие',
                'value' => $this->certificate->standard->name ?? ''
            ],
            [
                'title' => 'Договор поставки / номер спецификации/ номер позиции',
                'value' => $this->certificate->agreement_delivery ?? ''
            ],
            [
                'title' => 'Наружный диаметр',
                'unit' => 'мм',
                'value' => $this->certificate->outerDiameter->name ?? ''
            ],
            [
                'title' => 'Толщина стенки',
                'unit' => 'мм',
                'value' => implode('/', $queryWall->wall ?? null)
            ],
            [
                'title' => 'Группа прочности',
                'value' => $this->certificate->hardness
                    ? ((
                        $this->certificate->standard->prefix ?? ''
                        ) . $this->certificate->hardness->name
                    )
                    : ''
            ],
            [
                'title' => 'Длина',
                'unit' => 'м',
                'value' => implode('/', $queryWall->length ?? null)
            ],
            [
                'title' => 'Масса теоретическая',
                'unit' => 'тн',
                'value' => $this->certificate->getTheoreticalMass()
                    ? number_format(
                        round($this->certificate->getTheoreticalMass() / 1000, 3)
                    )
                    : ''],
            ['title' => 'Вид термической обработки', 'value' => $this->certificate->type_heat_treatment ?? ''],
            [
                'title' => 'Тип быстроразъемного соединения',
                'ref' => $this->certificate->type_quick_connection_note,
                'value' => $this->certificate->type_quick_connection ?? ''
            ],
            [
                'title' => 'Давление испытательное',
                'unit' => 'МПа',
                'value' => $this->certificate->getPressureTest()
                    ? number_format($this->certificate->getPressureTest(), 2)
                    : ''
            ],
            [
                'title' => 'Результат гидростатических испытаний',
                'value' => $this->certificate->result_hydrostatic_test ?? ''
            ],
            [
                'title' => 'Марка геофизического кабеля',
                'ref' => $this->certificate->brand_geophysical_cable_note,
                'value' => $this->certificate->brand_geophysical_cable ?? '-'
            ],
            [
                'title' => 'Длина геофизического кабеля',
                'unit' => 'м',
                'ref' => $this->certificate->length_geophysical_cable_note,
                'value' => $this->certificate->length_geophysical_cable
                    ? number_format($this->certificate->length_geophysical_cable)
                    : '-'
            ],
        ];
    }

    public function getCylinderData(): array
    {
        $cylinder = $this->certificate->cylinder;
        return [
            [
                'title' => 'Материал изготовления транспортировочного барабана (катушки)',
                'value' => $cylinder ? ($cylinder->material ?? '') : ''
            ],
            [
                'title' => 'Масса барабана, тн',
                'value' => $cylinder ? ($cylinder->weight ?? '') : ''
            ],
            [
                'title' => 'Диаметр сердечника барабана, мм',
                'value' => $cylinder ? ($cylinder->diameter_core ?? '') : ''
            ],
            [
                'title' => 'Габаритные размеры барабана:',
                'value' => '-'
            ],
            [
                'title' => ' - диаметр щек, мм',
                'value' => $cylinder ? ($cylinder->diameter_cheek ?? '') : ''
            ],
            [
                'title' => ' - ширина, мм',
                'value' => $cylinder ? ($cylinder->width ?? '') : ''
            ],
            [
                'title' => 'Отметка о заполнении внутренней полости ГНКТ азотом',
                'value' => $cylinder ? ($cylinder->mark_nitrogen ?? '') : ''
            ],
        ];
    }

    /**
     * @return mixed
     */
    public function getMinRelativeExtension(): mixed
    {
        $wallThicknessIds = [];

        foreach ($this->certificate->melds as $meld) {
            $wallThicknessIds = array_merge($wallThicknessIds, $meld->rolls->wall_thickness_id ?? null);
        }

        return RelativeExtension::query()
            ->where('standard_id', $this->certificate->standard_id)
            ->where('hardness_id', $this->certificate->hardness_id)
            ->where('outer_diameter_id', $this->certificate->outer_diameter_id)
            ->where('wall_thickness_id', $wallThicknessIds)
            ->min('value');
    }

    /**
     * @param bool $isAverage
     * @return mixed
     */
    public function getMinAbsorbedEnergy(bool $isAverage = false): mixed
    {
        $wallThicknessIds = [];

        foreach ($this->certificate->melds() as $meld) {
            $wallThicknessIds = array_merge($wallThicknessIds, $meld->rolls->wall_thickness_id ?? null);
        }

        return AbsorbedEnergyLimit::query()
            ->where('standard_id', $this->certificate->standard_id)
            ->where('wall_thickness_id', $wallThicknessIds)
            ->min($isAverage ? 'value_average' : 'value');
    }
}
