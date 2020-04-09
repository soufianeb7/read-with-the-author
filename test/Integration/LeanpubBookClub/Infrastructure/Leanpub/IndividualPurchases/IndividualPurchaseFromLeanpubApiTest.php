<?php
declare(strict_types=1);

namespace LeanpubBookClub\Infrastructure\Leanpub\IndividualPurchases;

use LeanpubBookClub\Infrastructure\Leanpub\ApiKey;
use LeanpubBookClub\Infrastructure\Leanpub\BookSlug;
use PHPUnit\Framework\TestCase;
use LeanpubBookClub\Infrastructure\Env;

/**
 * @group internet
 * @group slow
 */
final class IndividualPurchaseFromLeanpubApiTest extends TestCase
{
    /**
     * @test
     */
    public function it_loads_all_purchases_from_leanpub(): void
    {
        $individualPurchases = new IndividualPurchaseFromLeanpubApi(
            BookSlug::fromString('object-design'),
            ApiKey::fromString(Env::get('LEANPUB_API_KEY'))
        );

        $numberOfPurchases = 0;

        $lastPurchaseDate = null;

        foreach ($individualPurchases->all() as $purchase) {
            self::assertInstanceOf(Purchase::class, $purchase);
            /** @var Purchase $purchase */

            // Check that invoice ids have the format we expect them to have
            $purchase->invoiceId();

            if ($lastPurchaseDate !== null) {
                // Check that the purchases are sorted by purchase date descending
                self::assertTrue(strcmp($lastPurchaseDate, $purchase->datePurchased()) >= 0);
            }

            $lastPurchaseDate = $purchase->datePurchased();

            $numberOfPurchases++;
        }

        self::assertGreaterThan(50, $numberOfPurchases, 'The API client is supposed to fetch more than one page');
    }
}
