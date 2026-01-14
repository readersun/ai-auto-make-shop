<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header with Actions -->
        <div class="mb-8 flex items-center justify-between">
          <div>
            <Link href="/admin/products" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
              ← 목록으로 돌아가기
            </Link>
            <h1 class="text-3xl font-bold text-gray-900">{{ product.name }}</h1>
            <p class="mt-2 text-gray-600">AI가 기획한 상품 상세 정보</p>
          </div>

          <!-- Action Buttons -->
          <div class="flex space-x-3">
            <Link
              :href="`/admin/products/${product.id}/edit`"
              class="px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
              </svg>
              수정
            </Link>
            <button
              v-if="product.status === 'pending'"
              @click="rejectProduct"
              class="px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 transition flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              거부
            </button>
            <button
              v-if="product.status === 'pending'"
              @click="approveProduct"
              class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center"
            >
              <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              승인
            </button>
          </div>
        </div>

        <!-- Status Badge -->
        <div class="mb-6">
          <span
            :class="getStatusClass(product.status)"
            class="px-4 py-2 inline-flex text-sm leading-5 font-semibold rounded-full"
          >
            {{ getStatusText(product.status) }}
          </span>
        </div>

        <!-- Tabs -->
        <div class="bg-white rounded-lg shadow-sm overflow-hidden">
          <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
              <button
                v-for="tab in tabs"
                :key="tab.id"
                @click="activeTab = tab.id"
                :class="[
                  'px-6 py-4 text-sm font-medium border-b-2 transition',
                  activeTab === tab.id
                    ? 'border-blue-500 text-blue-600'
                    : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                ]"
              >
                {{ tab.name }}
              </button>
            </nav>
          </div>

          <div class="p-6">
            <!-- Tab: 기본 정보 -->
            <div v-if="activeTab === 'basic'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">상품명</label>
                  <div class="text-lg font-semibold text-gray-900">{{ product.name }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">카테고리</label>
                  <div class="text-lg text-gray-900">{{ product.category }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">타겟 고객</label>
                  <div class="text-gray-900">{{ product.target_customer }}</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">권장 판매가</label>
                  <div class="text-2xl font-bold text-green-600">
                    {{ formatPrice(product.recommended_price) }}
                  </div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">수익률</label>
                  <div class="text-xl font-bold text-blue-600">{{ product.profit_margin }}%</div>
                </div>
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">생성일시</label>
                  <div class="text-gray-900">{{ formatDateTime(product.created_at) }}</div>
                </div>
              </div>

              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">판매 포인트</label>
                <ul class="list-disc list-inside space-y-2 text-gray-900">
                  <li v-for="(point, index) in parseSellingPoints" :key="index">{{ point }}</li>
                </ul>
              </div>
            </div>

            <!-- Tab: 원가 분석 -->
            <div v-if="activeTab === 'cost'" class="space-y-6">
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gray-50 p-4 rounded-lg">
                  <label class="block text-sm font-medium text-gray-700 mb-2">총 원가</label>
                  <div class="text-2xl font-bold text-red-600">
                    {{ formatPrice(product.cost?.total_cost || 0) }}
                  </div>
                </div>
                <div class="bg-gray-50 p-4 rounded-lg">
                  <label class="block text-sm font-medium text-gray-700 mb-2">예상 순이익</label>
                  <div class="text-2xl font-bold text-green-600">
                    {{ formatPrice(calculateProfit()) }}
                  </div>
                </div>
              </div>

              <div v-if="product.cost" class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-white border border-gray-200 p-4 rounded-lg">
                  <label class="block text-xs text-gray-600 mb-1">상품 단가</label>
                  <div class="text-lg font-semibold">
                    {{ formatPrice(product.cost.product_unit_price || 0) }}
                  </div>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg">
                  <label class="block text-xs text-gray-600 mb-1">국제 배송비</label>
                  <div class="text-lg font-semibold">
                    {{ formatPrice(product.cost.international_shipping) }}
                  </div>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg">
                  <label class="block text-xs text-gray-600 mb-1">관세</label>
                  <div class="text-lg font-semibold">
                    {{ formatPrice(product.cost.customs_duty) }}
                  </div>
                </div>
                <div class="bg-white border border-gray-200 p-4 rounded-lg">
                  <label class="block text-xs text-gray-600 mb-1">국내 배송비</label>
                  <div class="text-lg font-semibold">
                    {{ formatPrice(product.cost.domestic_shipping) }}
                  </div>
                </div>
              </div>

              <div v-if="product.cost?.cost_notes" class="bg-yellow-50 border border-yellow-200 p-4 rounded-lg">
                <label class="block text-sm font-medium text-yellow-800 mb-2">원가 메모</label>
                <div class="text-sm text-yellow-900">{{ product.cost.cost_notes }}</div>
              </div>

              <div v-if="product.cost?.supplier_name" class="border-t pt-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">공급처 정보</h3>
                <div class="space-y-2">
                  <div>
                    <label class="text-sm text-gray-600">공급처명:</label>
                    <span class="ml-2 text-gray-900">{{ product.cost.supplier_name }}</span>
                  </div>
                  <div v-if="product.cost.supplier_url">
                    <label class="text-sm text-gray-600">URL:</label>
                    <a
                      :href="product.cost.supplier_url"
                      target="_blank"
                      class="ml-2 text-blue-600 hover:text-blue-800"
                    >
                      {{ product.cost.supplier_url }}
                    </a>
                  </div>
                </div>
              </div>
            </div>

            <!-- Tab: 콘텐츠 -->
            <div v-if="activeTab === 'content'" class="space-y-6">
              <div v-if="product.content">
                <div v-if="product.content.seo_title" class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">SEO 제목</label>
                  <div class="text-xl font-semibold text-gray-900">{{ product.content.seo_title }}</div>
                </div>

                <div v-if="product.content.header_copy" class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">헤더 문구</label>
                  <div class="text-lg text-gray-900">{{ product.content.header_copy }}</div>
                </div>

                <div v-if="product.content.key_features" class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">주요 특징</label>
                  <div class="prose max-w-none text-gray-900">{{ product.content.key_features }}</div>
                </div>

                <div v-if="product.content.full_description" class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">상세 설명</label>
                  <div class="prose max-w-none text-gray-900 whitespace-pre-wrap">
                    {{ product.content.full_description }}
                  </div>
                </div>

                <div v-if="product.content.usage_guide" class="mb-6 bg-blue-50 p-4 rounded-lg">
                  <label class="block text-sm font-medium text-blue-900 mb-2">사용 방법</label>
                  <div class="text-gray-900 whitespace-pre-wrap">{{ product.content.usage_guide }}</div>
                </div>

                <div v-if="product.content.precautions" class="mb-6 bg-yellow-50 p-4 rounded-lg">
                  <label class="block text-sm font-medium text-yellow-900 mb-2">주의사항</label>
                  <div class="text-gray-900">{{ product.content.precautions }}</div>
                </div>

                <div v-if="product.content.recommendation" class="mb-6">
                  <label class="block text-sm font-medium text-gray-700 mb-2">추천 대상</label>
                  <div class="text-gray-900">{{ product.content.recommendation }}</div>
                </div>
              </div>
              <div v-else class="text-center py-12 text-gray-500">
                콘텐츠 정보가 없습니다.
              </div>
            </div>

            <!-- Tab: 광고 문구 -->
            <div v-if="activeTab === 'ads'" class="space-y-6">
              <!-- Naver Ads -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <span class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white mr-3">
                    N
                  </span>
                  네이버 광고 문구
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div
                    v-for="ad in getAdsByPlatform('naver')"
                    :key="ad.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition"
                  >
                    <div class="text-xs text-gray-500 mb-2">버전 {{ ad.variation }}</div>
                    <div class="text-sm font-semibold text-gray-900 mb-2">{{ ad.headline }}</div>
                    <div class="text-sm text-gray-600">{{ ad.description }}</div>
                  </div>
                </div>
              </div>

              <!-- Google Ads -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <span class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center text-white mr-3">
                    G
                  </span>
                  구글 광고 문구
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div
                    v-for="ad in getAdsByPlatform('google')"
                    :key="ad.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition"
                  >
                    <div class="text-xs text-gray-500 mb-2">버전 {{ ad.variation }}</div>
                    <div class="text-sm font-semibold text-gray-900 mb-2">{{ ad.headline }}</div>
                    <div class="text-sm text-gray-600">{{ ad.description }}</div>
                  </div>
                </div>
              </div>

              <!-- Meta Ads -->
              <div>
                <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                  <span class="w-8 h-8 bg-purple-500 rounded-full flex items-center justify-center text-white mr-3">
                    M
                  </span>
                  메타 광고 문구 (페이스북/인스타)
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                  <div
                    v-for="ad in getAdsByPlatform('meta')"
                    :key="ad.id"
                    class="bg-white border border-gray-200 rounded-lg p-4 hover:shadow-md transition"
                  >
                    <div class="text-xs text-gray-500 mb-2">버전 {{ ad.variation }}</div>
                    <div class="text-sm font-semibold text-gray-900 mb-2">{{ ad.headline }}</div>
                    <div class="text-sm text-gray-600">{{ ad.description }}</div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  product: Object,
});

const activeTab = ref('basic');

const tabs = [
  { id: 'basic', name: '기본 정보' },
  { id: 'cost', name: '원가 분석' },
  { id: 'content', name: '콘텐츠' },
  { id: 'ads', name: '광고 문구' },
];

const parseSellingPoints = computed(() => {
  try {
    return JSON.parse(props.product.selling_points);
  } catch {
    return [];
  }
});

const formatPrice = (price) => {
  return new Intl.NumberFormat('ko-KR', {
    style: 'currency',
    currency: 'KRW',
  }).format(price);
};

const formatDateTime = (dateString) => {
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('ko-KR', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  }).format(date);
};

const calculateProfit = () => {
  const price = parseFloat(props.product.recommended_price);
  const cost = parseFloat(props.product.cost?.total_cost || 0);
  return price - cost;
};

const getAdsByPlatform = (platform) => {
  return props.product.ad_copies.filter((ad) => ad.platform === platform);
};

const getStatusClass = (status) => {
  const classes = {
    pending: 'bg-yellow-100 text-yellow-800',
    approved: 'bg-green-100 text-green-800',
    rejected: 'bg-red-100 text-red-800',
  };
  return classes[status] || 'bg-gray-100 text-gray-800';
};

const getStatusText = (status) => {
  const texts = {
    pending: '검토 대기',
    approved: '승인됨',
    rejected: '거부됨',
  };
  return texts[status] || status;
};

const approveProduct = () => {
  if (confirm('이 상품을 승인하시겠습니까?')) {
    router.post(`/admin/products/${props.product.id}/approve`, {}, {
      onSuccess: () => {
        alert('상품이 승인되었습니다.');
      },
    });
  }
};

const rejectProduct = () => {
  if (confirm('이 상품을 거부하시겠습니까?')) {
    router.post(`/admin/products/${props.product.id}/reject`, {}, {
      onSuccess: () => {
        alert('상품이 거부되었습니다.');
      },
    });
  }
};
</script>
