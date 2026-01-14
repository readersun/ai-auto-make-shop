<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductCost;
use App\Models\ProductContent;
use App\Models\ProductAdCopy;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProductPlannerService
{
    protected OpenAIService $openAI;

    public function __construct(OpenAIService $openAI)
    {
        $this->openAI = $openAI;
    }

    /**
     * 전체 상품 기획 프로세스 실행
     */
    public function planProduct(): ?Product
    {
        DB::beginTransaction();

        try {
            $sessionId = Str::uuid()->toString();

            // STEP 1: 상품 기획
            $productData = $this->step1_generateProductIdea($sessionId);
            if (!$productData) {
                throw new \Exception('Failed to generate product idea');
            }

            // STEP 2: 원가 분석
            $costData = $this->step2_analyzeCost($productData);
            if (!$costData) {
                throw new \Exception('Failed to analyze cost');
            }

            // STEP 3: 콘텐츠 생성
            $contentData = $this->step3_generateContent($productData);
            if (!$contentData) {
                throw new \Exception('Failed to generate content');
            }

            // STEP 4: 광고 문구 생성
            $adCopies = $this->step4_generateAdCopies($productData);
            if (!$adCopies) {
                throw new \Exception('Failed to generate ad copies');
            }

            // STEP 5: 데이터베이스 저장
            $product = $this->step5_saveToDatabase(
                $productData,
                $costData,
                $contentData,
                $adCopies,
                $sessionId
            );

            DB::commit();

            Log::info('Product planning completed', ['product_id' => $product->id]);

            return $product;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Product planning failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * STEP 1: AI 상품 기획
     */
    protected function step1_generateProductIdea(string $sessionId): ?array
    {
        $prompt = <<<EOT
당신은 전문 쇼핑몰 상품 기획자입니다.
현재 트렌드를 반영한 독창적이고 실용적인 상품 아이디어를 하나 제안해주세요.

다음 정보를 JSON 형식으로 제공해주세요:
{
  "name": "상품명 (한글, 20자 이내)",
  "category": "카테고리 (예: 전자기기, 패션잡화, 생활용품, 뷰티)",
  "target_customer": "타겟 고객층 설명 (100자 이내)",
  "selling_points": "핵심 판매 포인트 3가지 (각 50자 이내, 배열로)",
  "recommended_price": "권장 판매가 (숫자만, 원 단위)",
  "profit_margin": "예상 수익률 (0-100 사이 숫자)"
}

실제로 판매 가능한 현실적인 상품을 제안해주세요.
EOT;

        $result = $this->openAI->generateJson($prompt, [
            'model' => 'gpt-4',
            'temperature' => 0.8,
            'system' => '당신은 창의적이고 현실적인 상품 기획 전문가입니다. 트렌드를 반영하면서도 실현 가능한 아이디어를 제안합니다.',
        ]);

        if ($result && isset($result['name'])) {
            // selling_points를 문자열로 변환 (저장용)
            if (is_array($result['selling_points'])) {
                $result['selling_points_array'] = $result['selling_points'];
                $result['selling_points'] = json_encode($result['selling_points'], JSON_UNESCAPED_UNICODE);
            }

            $result['session_id'] = $sessionId;

            Log::info('STEP 1 - Product idea generated', ['product_name' => $result['name']]);
            return $result;
        }

        return null;
    }

    /**
     * STEP 2: 원가 분석
     */
    protected function step2_analyzeCost(array $productData): ?array
    {
        $productName = $productData['name'];
        $category = $productData['category'];
        $recommendedPrice = $productData['recommended_price'];

        $prompt = <<<EOT
상품명: {$productName}
카테고리: {$category}
판매가: {$recommendedPrice}원

이 상품의 원가 구조를 분석하고 다음 정보를 JSON 형식으로 제공해주세요:

{
  "material_cost": "재료비 (숫자)",
  "manufacturing_cost": "제조비 (숫자)",
  "shipping_cost": "배송비 (숫자)",
  "platform_fee": "플랫폼 수수료 (숫자)",
  "total_cost": "총 원가 (숫자)",
  "expected_profit": "예상 순이익 (숫자)",
  "cost_breakdown": "원가 상세 설명 (200자 이내)"
}

현실적인 원가를 산출해주세요. 총 원가는 판매가의 40-60% 수준이 적절합니다.
EOT;

        $result = $this->openAI->generateJson($prompt, [
            'model' => 'gpt-4',
            'temperature' => 0.5,
            'system' => '당신은 정확한 원가 분석 전문가입니다. 시장 조사를 바탕으로 현실적인 원가를 산출합니다.',
        ]);

        if ($result && isset($result['total_cost'])) {
            Log::info('STEP 2 - Cost analysis completed', ['total_cost' => $result['total_cost']]);
            return $result;
        }

        return null;
    }

    /**
     * STEP 3: 상품 상세 콘텐츠 생성
     */
    protected function step3_generateContent(array $productData): ?array
    {
        $productName = $productData['name'];
        $category = $productData['category'];
        $targetCustomer = $productData['target_customer'];
        $sellingPoints = $productData['selling_points_array'] ?? [];

        $sellingPointsText = implode(', ', $sellingPoints);

        $prompt = <<<EOT
상품명: {$productName}
카테고리: {$category}
타겟 고객: {$targetCustomer}
핵심 포인트: {$sellingPointsText}

위 정보를 바탕으로 쇼핑몰 상세 페이지용 콘텐츠를 작성해주세요.
JSON 형식으로 제공해주세요:

{
  "title": "매력적인 상품 제목 (30자 이내)",
  "subtitle": "부제목 (50자 이내)",
  "description": "상세 설명 (500-1000자, 마크다운 형식 가능)",
  "features": "주요 특징 리스트 (배열, 각 100자 이내)",
  "usage_guide": "사용 방법 (300자 이내)",
  "caution": "주의사항 (200자 이내)"
}

설득력 있고 구매욕을 자극하는 콘텐츠를 작성해주세요.
EOT;

        $result = $this->openAI->generateJson($prompt, [
            'model' => 'gpt-4',
            'temperature' => 0.7,
            'system' => '당신은 전문 카피라이터입니다. 고객의 마음을 사로잡는 매력적인 상품 설명을 작성합니다.',
        ]);

        if ($result && isset($result['title'])) {
            // features를 문자열로 변환 (저장용)
            if (isset($result['features']) && is_array($result['features'])) {
                $result['features'] = json_encode($result['features'], JSON_UNESCAPED_UNICODE);
            }

            Log::info('STEP 3 - Content generated', ['title' => $result['title']]);
            return $result;
        }

        return null;
    }

    /**
     * STEP 4: 광고 문구 생성 (네이버, 구글, 메타 각 3개씩)
     */
    protected function step4_generateAdCopies(array $productData): ?array
    {
        $productName = $productData['name'];
        $targetCustomer = $productData['target_customer'];
        $sellingPoints = $productData['selling_points_array'] ?? [];

        $sellingPointsText = implode(', ', $sellingPoints);

        $prompt = <<<EOT
상품명: {$productName}
타겟 고객: {$targetCustomer}
핵심 포인트: {$sellingPointsText}

위 상품을 위한 광고 문구를 작성해주세요.
각 플랫폼별로 3개씩, 총 9개의 광고 문구를 JSON 형식으로 제공해주세요:

{
  "naver": [
    {"headline": "제목 (15자 이내)", "description": "설명 (45자 이내)"},
    {"headline": "제목", "description": "설명"},
    {"headline": "제목", "description": "설명"}
  ],
  "google": [
    {"headline": "제목 (30자 이내)", "description": "설명 (90자 이내)"},
    {"headline": "제목", "description": "설명"},
    {"headline": "제목", "description": "설명"}
  ],
  "meta": [
    {"headline": "제목 (40자 이내)", "description": "설명 (125자 이내)"},
    {"headline": "제목", "description": "설명"},
    {"headline": "제목", "description": "설명"}
  ]
}

각 플랫폼의 특성에 맞는 효과적인 광고 문구를 작성해주세요.
EOT;

        $result = $this->openAI->generateJson($prompt, [
            'model' => 'gpt-4',
            'temperature' => 0.8,
            'system' => '당신은 디지털 마케팅 전문가입니다. 각 광고 플랫폼의 특성을 이해하고 클릭률을 높이는 문구를 작성합니다.',
        ]);

        if ($result && isset($result['naver']) && isset($result['google']) && isset($result['meta'])) {
            Log::info('STEP 4 - Ad copies generated', [
                'naver_count' => count($result['naver']),
                'google_count' => count($result['google']),
                'meta_count' => count($result['meta']),
            ]);
            return $result;
        }

        return null;
    }

    /**
     * STEP 5: 데이터베이스 저장
     */
    protected function step5_saveToDatabase(
        array $productData,
        array $costData,
        array $contentData,
        array $adCopiesData,
        string $sessionId
    ): Product {
        // 1. Product 저장
        $product = Product::create([
            'name' => $productData['name'],
            'category' => $productData['category'],
            'target_customer' => $productData['target_customer'],
            'selling_points' => $productData['selling_points'],
            'recommended_price' => $productData['recommended_price'],
            'profit_margin' => $productData['profit_margin'],
            'is_approved' => false,
            'is_active' => false,
            'status' => 'pending',
            'ai_session_id' => $sessionId,
        ]);

        // 2. ProductCost 저장
        ProductCost::create([
            'product_id' => $product->id,
            'material_cost' => $costData['material_cost'],
            'manufacturing_cost' => $costData['manufacturing_cost'],
            'shipping_cost' => $costData['shipping_cost'],
            'platform_fee' => $costData['platform_fee'],
            'total_cost' => $costData['total_cost'],
            'expected_profit' => $costData['expected_profit'],
            'cost_breakdown' => $costData['cost_breakdown'],
        ]);

        // 3. ProductContent 저장
        ProductContent::create([
            'product_id' => $product->id,
            'title' => $contentData['title'],
            'subtitle' => $contentData['subtitle'],
            'description' => $contentData['description'],
            'features' => $contentData['features'],
            'usage_guide' => $contentData['usage_guide'],
            'caution' => $contentData['caution'],
        ]);

        // 4. ProductAdCopy 저장 (9개)
        $adCopies = [];

        foreach ($adCopiesData['naver'] as $index => $copy) {
            $adCopies[] = [
                'product_id' => $product->id,
                'platform' => 'naver',
                'headline' => $copy['headline'],
                'description' => $copy['description'],
                'variation' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($adCopiesData['google'] as $index => $copy) {
            $adCopies[] = [
                'product_id' => $product->id,
                'platform' => 'google',
                'headline' => $copy['headline'],
                'description' => $copy['description'],
                'variation' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        foreach ($adCopiesData['meta'] as $index => $copy) {
            $adCopies[] = [
                'product_id' => $product->id,
                'platform' => 'meta',
                'headline' => $copy['headline'],
                'description' => $copy['description'],
                'variation' => $index + 1,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        ProductAdCopy::insert($adCopies);

        Log::info('STEP 5 - Data saved to database', [
            'product_id' => $product->id,
            'ad_copies_count' => count($adCopies),
        ]);

        return $product->load(['cost', 'content', 'adCopies']);
    }
}
