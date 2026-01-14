# Phase 2-2 완료: AI 기획 엔진 개발

## 개요
OpenAI GPT-4를 활용한 완전 자동 상품 기획 시스템이 완성되었습니다.

## 구현된 파일

### 1. OpenAIService ([app/Services/OpenAIService.php](app/Services/OpenAIService.php))
OpenAI API를 쉽게 사용할 수 있는 래퍼 서비스입니다.

**주요 메서드:**
- `generateText(string $prompt, array $options)` - 텍스트 생성
- `generateJson(string $prompt, array $options)` - JSON 형식 응답 생성
- `chat(array $messages, array $options)` - 대화형 생성

**특징:**
- 에러 핸들링 및 로깅
- JSON 형식 응답 자동 파싱
- 옵션을 통한 유연한 설정 (모델, 온도, 최대 토큰 등)

### 2. ProductPlannerService ([app/Services/ProductPlannerService.php](app/Services/ProductPlannerService.php))
5단계 상품 기획 프로세스를 자동으로 실행하는 메인 서비스입니다.

#### STEP 1: 상품 아이디어 생성
```php
protected function step1_generateProductIdea(string $sessionId): ?array
```
- GPT-4가 트렌드를 반영한 독창적인 상품 제안
- 상품명, 카테고리, 타겟 고객, 판매 포인트, 가격, 수익률
- JSON 형식으로 구조화된 데이터 반환

#### STEP 2: 원가 분석
```php
protected function step2_analyzeCost(array $productData): ?array
```
- 재료비, 제조비, 배송비, 플랫폼 수수료 계산
- 총 원가 및 예상 순이익 산출
- 판매가의 40-60% 수준으로 현실적인 원가 설정

#### STEP 3: 상세 콘텐츠 생성
```php
protected function step3_generateContent(array $productData): ?array
```
- 쇼핑몰 상세 페이지용 콘텐츠 자동 작성
- 제목, 부제목, 상세 설명 (500-1000자)
- 주요 특징, 사용 방법, 주의사항

#### STEP 4: 광고 문구 생성
```php
protected function step4_generateAdCopies(array $productData): ?array
```
- 각 플랫폼별 특성에 맞는 광고 문구 생성
- **네이버 광고**: 제목 15자, 설명 45자 (3개)
- **구글 광고**: 제목 30자, 설명 90자 (3개)
- **메타 광고**: 제목 40자, 설명 125자 (3개)

#### STEP 5: 데이터베이스 저장
```php
protected function step5_saveToDatabase(...): Product
```
- 4개 테이블에 데이터 저장 (Product, ProductCost, ProductContent, ProductAdCopy)
- 트랜잭션 처리로 데이터 일관성 보장
- 에러 발생 시 자동 롤백

### 3. ProductPlanCommand ([app/Console/Commands/ProductPlanCommand.php](app/Console/Commands/ProductPlanCommand.php))
CLI에서 상품 기획을 실행할 수 있는 Artisan 명령어입니다.

**사용법:**
```bash
php artisan product:plan
```

**기능:**
- OpenAI API 키 설정 확인
- 5단계 진행 상황 표시
- 완료된 상품 정보 출력
- 에러 발생 시 로그 위치 안내

## 데이터 흐름

```
1. php artisan product:plan 실행
   ↓
2. ProductPlannerService::planProduct() 호출
   ↓
3. STEP 1: AI가 상품 아이디어 생성
   ↓
4. STEP 2: 원가 구조 분석
   ↓
5. STEP 3: 상세 콘텐츠 작성
   ↓
6. STEP 4: 광고 문구 9개 생성 (네이버/구글/메타 각 3개)
   ↓
7. STEP 5: 데이터베이스 저장
   ↓
8. 완료된 상품 정보 반환
```

## 환경 설정

### .env 파일
```env
OPENAI_API_KEY=your-api-key-here
OPENAI_MODEL=gpt-4
OPENAI_MAX_TOKENS=4000
```

### 서비스 등록 ([app/Providers/AppServiceProvider.php](app/Providers/AppServiceProvider.php))
```php
$this->app->singleton(\App\Services\OpenAIService::class);
$this->app->singleton(\App\Services\ProductPlannerService::class);
```

## 설치된 패키지
```json
{
  "openai-php/laravel": "^0.18.0"
}
```

## 테스트 방법

### 1. OpenAI API 키 설정
```bash
# .env 파일 편집
OPENAI_API_KEY=sk-...
```

### 2. 데이터베이스 마이그레이션
```bash
php artisan migrate
```

### 3. 상품 기획 실행
```bash
php artisan product:plan
```

### 4. 결과 확인
명령어 실행 후 다음 정보가 표시됩니다:
- 상품명, 카테고리, 가격, 수익률
- 총 원가, 예상 순이익
- 콘텐츠 제목
- 생성된 광고 문구 개수
- Product ID, Session ID

### 5. 데이터베이스 확인
```bash
# PostgreSQL 접속
psql -U postgres -d ai_auto_shop

# 생성된 데이터 확인
SELECT * FROM products;
SELECT * FROM product_costs;
SELECT * FROM product_contents;
SELECT * FROM product_ad_copies;
```

## 기술적 특징

### 1. 트랜잭션 처리
```php
DB::beginTransaction();
try {
    // ... 5단계 처리
    DB::commit();
} catch (\Exception $e) {
    DB::rollBack();
    Log::error('Product planning failed: ' . $e->getMessage());
}
```

### 2. JSON 응답 형식
OpenAI의 Structured Output 기능 활용:
```php
'response_format' => ['type' => 'json_object']
```

### 3. 에러 핸들링
- 각 단계별 실패 시 명확한 에러 메시지
- 로그 기록으로 디버깅 지원
- 트랜잭션 롤백으로 데이터 정합성 유지

### 4. 유연한 프롬프트 설계
- System 메시지로 AI 역할 정의
- Temperature 조절로 창의성/일관성 제어
- 명확한 출력 형식 지시

## 활용 예시

### 생성된 상품 예시
```
📦 상품명: 스마트 무선 이어폰 프로
🏷️ 카테고리: 전자기기
💵 권장가: 89,000원
💹 수익률: 45%

💰 총 원가: 49,000원
💸 예상 순이익: 40,000원

📄 콘텐츠 제목: "프리미엄 사운드의 새로운 기준"

📢 광고 문구 생성: 9개
   - 네이버: 3개
   - 구글: 3개
   - 메타: 3개
```

## 다음 단계

Phase 3에서 구현할 내용:
1. 관리자 상품 목록 페이지 (Vue 3 + Inertia)
2. 상품 상세 페이지 (정보 확인)
3. 상품 승인/거부 기능
4. 상품 수정 기능

## 주의사항

### OpenAI API 비용
- GPT-4 사용으로 API 호출 비용 발생
- 1회 상품 기획 시 약 $0.10-0.30 예상
- 테스트 시 비용 모니터링 필요

### API 키 보안
- `.env` 파일을 절대 Git에 커밋하지 말 것
- `.gitignore`에 `.env` 포함 확인
- 프로덕션 환경에서는 환경 변수로 관리

### 데이터 검증
- AI 생성 데이터는 반드시 관리자 검토 필요
- 가격, 원가 정보는 실제 시장 조사로 검증
- 광고 문구는 플랫폼 정책 준수 확인

## 문제 해결

### OpenAI API 에러
```bash
# 로그 확인
tail -f storage/logs/laravel.log

# API 키 확인
php artisan tinker
> config('openai.api_key')
```

### 데이터베이스 연결 실패
```bash
# PostgreSQL 상태 확인
pg_isready

# 마이그레이션 상태 확인
php artisan migrate:status
```

## 성과
- ✅ 완전 자동 상품 기획 시스템 구축
- ✅ 5단계 AI 프로세스 구현
- ✅ 3개 플랫폼 광고 문구 자동 생성
- ✅ 트랜잭션 기반 안정적인 데이터 저장
- ✅ CLI 테스트 도구 제공

---

작업 완료일: 2026-01-14
개발자: AI Assistant
