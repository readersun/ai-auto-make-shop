# AI Auto Shop - 초기 설정 완료

## 🎉 Phase 1 완료 사항

✅ Laravel 12 프로젝트 생성
✅ Inertia.js 설치 및 설정
✅ Vue 3 + Vite 설정
✅ Tailwind CSS 설치 및 설정
✅ PostgreSQL 및 Redis 연결 설정
✅ 기본 폴더 구조 및 예제 페이지 생성
✅ Git 저장소 초기화

---

## 🚀 개발 서버 실행 방법

### 1. PostgreSQL 설정 (선택 사항)
현재는 SQLite로 설정되어 있습니다. PostgreSQL 사용 시:

```bash
# PostgreSQL 데이터베이스 생성
createdb ai_auto_shop

# .env 파일에서 DB_PASSWORD 설정
```

### 2. 개발 서버 시작

**터미널 1 - Laravel 서버:**
```bash
php artisan serve
```

**터미널 2 - Vite 개발 서버:**
```bash
npm run dev
```

### 3. 브라우저에서 접속
- 메인: http://localhost:8000
- 관리자: http://localhost:8000/admin

---

## 📁 프로젝트 구조

```
ai-auto-make-shop/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/       # 관리자 컨트롤러
│   │   │   └── Shop/        # 쇼핑몰 컨트롤러
│   │   └── Middleware/
│   │       └── HandleInertiaRequests.php
│   ├── Models/              # Eloquent 모델
│   └── Services/
│       ├── AI/              # AI 기획 엔진
│       └── Wholesale/       # 도매 연동
├── resources/
│   ├── js/
│   │   ├── Components/      # Vue 공통 컴포넌트
│   │   │   └── AppLayout.vue
│   │   └── Pages/
│   │       ├── Admin/       # 관리자 페이지
│   │       │   └── Dashboard.vue
│   │       ├── Shop/        # 쇼핑몰 페이지
│   │       └── Welcome.vue
│   └── views/
│       └── app.blade.php    # Inertia 루트 템플릿
├── routes/
│   └── web.php              # 라우트 정의
└── database/
    └── migrations/          # 데이터베이스 마이그레이션
```

---

## 🔧 환경 설정 (.env)

### 필수 설정
```env
APP_NAME="AI Auto Shop"
APP_URL=http://localhost:8000

# 데이터베이스 (현재 SQLite 사용)
DB_CONNECTION=pgsql
DB_DATABASE=ai_auto_shop

# 캐시 및 큐 (Redis)
CACHE_STORE=redis
QUEUE_CONNECTION=redis

# AI 설정 (나중에 추가)
OPENAI_API_KEY=your_key_here
OPENAI_MODEL=gpt-4
```

---

## 📋 다음 단계 (Phase 2)

이제 AI 기획 엔진을 개발할 준비가 되었습니다:

1. **ProductPlannerService 구현**
   - STEP 1-5 로직을 메서드로 분리
   - OpenAI API 연동

2. **데이터베이스 설계**
   - products 테이블
   - product_costs 테이블
   - product_contents 테이블
   - product_ad_copies 테이블

3. **관리자 UI 개발**
   - AI 기획 실행 페이지
   - 상품 관리 페이지

---

## 🛠 유용한 명령어

```bash
# Composer 패키지 설치
composer install

# NPM 패키지 설치
npm install

# 데이터베이스 마이그레이션
php artisan migrate

# 캐시 클리어
php artisan cache:clear
php artisan config:clear
php artisan route:clear

# 큐 워커 실행 (백그라운드 작업)
php artisan queue:work

# Artisan 명령어 목록
php artisan list
```

---

## 📦 설치된 주요 패키지

### Backend
- Laravel 12
- Inertia.js Laravel Adapter
- Laravel Pail (로그 뷰어)

### Frontend
- Vue 3
- @inertiajs/vue3
- Tailwind CSS 4
- Vite 7

---

## 🤝 다음 작업 요청 방법

Phase 2를 시작하려면:

```
"ProductPlannerService 만들어줘. STEP 1-5 로직을 메서드로 분리하고 OpenAI API 연동해줘"
```

또는 데이터베이스부터:

```
"상품 관련 마이그레이션 4개 만들어줘"
```
