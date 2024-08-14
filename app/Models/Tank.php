<?php

namespace App\Models;

use App\Common\AccountCategory;
use App\Common\FuelMatixCategoryType;
use App\Common\Module;
use App\Http\Controllers\TransactionController;
use App\Repository\CategoryRepository;
use App\Repository\TankRepository;
use App\Repository\TransactionRepository;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tank extends Model
{
    use HasFactory;
    protected $table = 'tank';
    public $timestamps = false;
    public function last_reading()
    {
        return $this->hasOne(TankLog::class, 'tank_id', 'id');
    }

    /**
     * Adds opening stock for the given asset category.
     *
     * This method performs several operations to add the opening stock for a tank. It first finds the height of the tank
     * corresponding to the opening stock volume and then saves a reading for the tank refill. It also handles the creation
     * or updating of a stock category associated with the tank and manages the opening balance for the stock category. If
     * an opening stock is provided, it creates a transaction to reflect the opening balance.
     *
     * @param Category $assetCategory The asset category associated with the opening stock.
     * @return bool Returns true if the operation is successful.
     */
    public function addOpeningStock(Category $assetCategory): bool
    {
        //Find the product by product id
        $product = Product::where('id', $this['product_id'])->first();
        // Find the tank height corresponding to the opening stock volume
        $tankHeight = self::findHeight($this['id'], $this['opening_stock']);

        // Save the tank refill reading
        TankRepository::readingSave([
            'tank_id' => $this['id'],
            'date' => Carbon::now('UTC'),
            'height' => $tankHeight,
            'type' => 'opening stock',
            'volume' => $this['opening_stock']
        ]);

        // Find the stock category associated with the tank
        $stockCategory = Category::where('module', Module::TANK)
            ->where('client_company_id', $this['client_company_id'])
            ->where('module_id', $this['id'])
            ->where('type', FuelMatixCategoryType::ASSET)
            ->first();

        // Prepare category data for creation or update
        $categoryData = [
            'name' => $this['tank_name'],
            'opening_balance' => $this['opening_stock'] ?? null,
            'module' => Module::TANK,
            'module_id' => $this['id']
        ];

        // Save or update the stock category
        if (!$stockCategory instanceof Category) {
            $stockCategory = CategoryRepository::saveCategory($categoryData, $assetCategory['id'], Module::TANK);
        } else {
            $stockCategory = CategoryRepository::updateCategory($stockCategory, $categoryData);
        }

        // Delete the opening balance for the stock category
        $deleteResponse = $stockCategory->deleteOpeningBalance();

        if ($deleteResponse) {
            // Create a transaction to reflect the opening balance if opening stock is provided
            if (!empty($this['opening_stock'])) {
                $retainEarning = Category::where('client_company_id', $this['client_company_id'])
                    ->where('slug', strtolower(AccountCategory::RETAIN_EARNING))
                    ->first();

                if ($retainEarning instanceof Category) {
                    $amount = $this['opening_stock'] * $product['buying_price'];
                    $transactionData['linked_id'] = $stockCategory['id'];
                    $transactionData = [
                        ['date' => "1970-01-01", 'account_id' => $stockCategory['id'], 'debit_amount' => $amount, 'credit_amount' => 0, 'opening_balance' => 1],
                        ['date' => "1970-01-01", 'account_id' => $retainEarning['id'], 'debit_amount' => 0, 'credit_amount' => $amount, 'opening_balance' => 1],
                    ];
                    TransactionRepository::saveTransaction($transactionData);
                }
            }
        }

        return true;
    }
    /**
     * Find the height for a given volume and tank ID.
     *
     * This method calculates the height of a liquid in a tank based on the volume provided.
     * It first checks if there is an exact match for the volume in the `bsti_chart` table.
     * If an exact match is found, it returns the corresponding height.
     * If no exact match is found, it performs a linear interpolation between the closest
     * lower and higher volume entries to estimate the height.
     *
     * @param int $tank_id The ID of the tank.
     * @param float $volume The volume of the liquid in the tank.
     * @return float|int The calculated height corresponding to the given volume.
     */
    public static function findHeight(int $tank_id, float $volume)
    {
        // Attempt to find an exact match for the volume in the bsti_chart table
        $bstiChart = BstiChart::where('tank_id', $tank_id)
            ->where('volume', '=', floor($volume))
            ->first();

        // If an exact match is found, return the corresponding height
        if ($bstiChart instanceof BstiChart) {
            return $bstiChart->height;
        }

        // Find the closest lower volume entry
        $lower_result = BstiChart::where('tank_id', $tank_id)
            ->where('volume', '<=', $volume)
            ->orderBy('id', 'DESC')
            ->first();

        // Find the closest higher volume entry
        $higher_result = BstiChart::where('tank_id', $tank_id)
            ->where('volume', '>=', $volume)
            ->orderBy('id', 'ASC')
            ->first();

        // If both lower and higher entries are found, perform linear interpolation
        if ($lower_result instanceof BstiChart && $higher_result instanceof BstiChart) {
            $height_fraction = ($volume - $lower_result->volume) / ($higher_result->volume - $lower_result->volume);
            return $lower_result->height + ($height_fraction * ($higher_result->height - $lower_result->height));
        }

        // Return 0 if no valid height can be calculated
        return 0;
    }

    /**
     * Find the volume for a given height and tank ID.
     *
     * @param float $height
     * @return float|int The calculated height corresponding to the given volume.
     */
    public function findVolume(float $height)
    {
        // Attempt to find an exact match for the volume in the bsti_chart table
        $bstiChart = BstiChart::where('tank_id', $this['id'])
            ->where('height', '=', floor($height))
            ->first();
        // If an exact match is found, return the corresponding height
        if ($bstiChart instanceof BstiChart) {
            return $bstiChart->volume;
        }

        // Find the closest lower volume entry
        $lower_result = BstiChart::where('tank_id', $this['id'])
            ->where('height', '<=', $height)
            ->orderBy('id', 'DESC')
            ->first();

        // Find the closest higher volume entry
        $higher_result = BstiChart::where('tank_id', $this['id'])
            ->where('height', '>=', $height)
            ->orderBy('id', 'ASC')
            ->first();

        // If both lower and higher entries are found, perform linear interpolation
        if ($lower_result instanceof BstiChart && $higher_result instanceof BstiChart) {
            $height_fraction = ($height - $lower_result->height) / ($higher_result->height - $lower_result->height);
            return $lower_result->volume + ($height_fraction * ($higher_result->volume - $lower_result->volume));
        }
        // Return 0 if no valid height can be calculated
        return 0;

    }
}
