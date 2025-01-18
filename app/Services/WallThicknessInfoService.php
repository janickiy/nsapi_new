<?php

namespace App\Services;

use App\Models\References\{
    FluidityLimit,
    StrengthLimit,
    AbsorbedEnergyLimit,
    TheoreticalMass,
    RelativeExtension,
    WallThickness,
};
use App\Models\Certificates\Certificate;

class WallThicknessInfoService
{
    /**
     * @var Certificate
     */
    protected Certificate $certificate;

    /**
     * @var WallThickness|null
     */
    private ?WallThickness $wallThickness;

    public function __construct(Certificate $certificate, ?int $wallThicknessId)
    {
        $this->certificate = $certificate;
        $this->wallThickness = WallThickness::find($wallThicknessId);
    }

    /**
     * @return array
     */
    public function fields(): array
    {
        $strengthLimit = StrengthLimit::findByCertificate($this->certificate);
        $fluidityLimit = FluidityLimit::findByCertificate($this->certificate);

        $absorbedEnergyLimit = AbsorbedEnergyLimit::findByWallThickness($this->wallThickness, $this->certificate);
        $theoreticalMass   = TheoreticalMass::findByWallThickness($this->wallThickness, $this->certificate);
        $relativeExtension = RelativeExtension::findByWallThickness(
            $this->wallThickness,
            $this->certificate
        );

        return $this->wallThickness ? [
            'theoretical_mass' => $theoreticalMass?->value,
            'strength_min' => $strengthLimit?->value,
            'fluidity_min' => $fluidityLimit?->value_min,
            'fluidity_max' => $fluidityLimit?->value_max,
            'relative_extension_min' => $relativeExtension?->value,
            'absorbed_energy_1_min'  => $absorbedEnergyLimit?->value,
            'absorbed_energy_2_min'  => $absorbedEnergyLimit?->value,
            'absorbed_energy_3_min'  => $absorbedEnergyLimit?->value
        ] : [];
    }
}
