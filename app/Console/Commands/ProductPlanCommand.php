<?php

namespace App\Console\Commands;

use App\Services\ProductPlannerService;
use Illuminate\Console\Command;

class ProductPlanCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'product:plan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'AI를 사용하여 새로운 상품을 기획합니다';

    /**
     * Execute the console command.
     */
    public function handle(ProductPlannerService $planner): int
    {
        $this->info('🤖 AI 상품 기획을 시작합니다...');
        $this->newLine();

        // API 키 확인
        if (empty(config('openai.api_key'))) {
            $this->error('❌ OpenAI API 키가 설정되지 않았습니다.');
            $this->warn('📝 .env 파일에 OPENAI_API_KEY를 설정해주세요.');
            return self::FAILURE;
        }

        $this->info('📝 STEP 1: 상품 아이디어 생성 중...');
        $this->info('💰 STEP 2: 원가 분석 중...');
        $this->info('✍️  STEP 3: 상세 콘텐츠 작성 중...');
        $this->info('📢 STEP 4: 광고 문구 생성 중...');
        $this->info('💾 STEP 5: 데이터베이스 저장 중...');
        $this->newLine();

        $product = $planner->planProduct();

        if (!$product) {
            $this->error('❌ 상품 기획에 실패했습니다.');
            $this->warn('로그를 확인해주세요: storage/logs/laravel.log');
            return self::FAILURE;
        }

        $this->newLine();
        $this->info('✅ 상품 기획이 완료되었습니다!');
        $this->newLine();

        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->line("📦 상품명: <fg=cyan>{$product->name}</>");
        $this->line("🏷️  카테고리: {$product->category}");
        $this->line("💵 권장가: " . number_format($product->recommended_price) . "원");
        $this->line("💹 수익률: {$product->profit_margin}%");
        $this->line('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━');
        $this->newLine();

        if ($product->cost) {
            $this->line("💰 총 원가: " . number_format($product->cost->total_cost) . "원");
            $this->line("💸 예상 순이익: " . number_format($product->cost->expected_profit) . "원");
            $this->newLine();
        }

        if ($product->content) {
            $this->line("📄 콘텐츠 제목: <fg=yellow>{$product->content->title}</>");
            $this->newLine();
        }

        if ($product->adCopies->isNotEmpty()) {
            $this->line("📢 광고 문구 생성: {$product->adCopies->count()}개");
            $this->line("   - 네이버: " . $product->adCopies->where('platform', 'naver')->count() . "개");
            $this->line("   - 구글: " . $product->adCopies->where('platform', 'google')->count() . "개");
            $this->line("   - 메타: " . $product->adCopies->where('platform', 'meta')->count() . "개");
            $this->newLine();
        }

        $this->info("🔑 Product ID: {$product->id}");
        $this->info("🆔 Session ID: {$product->ai_session_id}");
        $this->newLine();

        return self::SUCCESS;
    }
}
