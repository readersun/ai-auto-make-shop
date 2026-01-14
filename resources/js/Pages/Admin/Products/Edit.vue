<template>
  <AppLayout>
    <div class="min-h-screen bg-gray-50 py-8">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
          <Link :href="`/admin/products/${product.id}`" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
            ← 상세로 돌아가기
          </Link>
          <h1 class="text-3xl font-bold text-gray-900">상품 수정</h1>
          <p class="mt-2 text-gray-600">{{ product.name }}</p>
        </div>

        <form @submit.prevent="submitForm">
          <!-- Tabs -->
          <div class="bg-white rounded-lg shadow-sm overflow-hidden mb-6">
            <div class="border-b border-gray-200">
              <nav class="flex -mb-px">
                <button
                  v-for="tab in tabs"
                  :key="tab.id"
                  type="button"
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
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      상품명 <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.name"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      카테고리 <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.category"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      타겟 고객 <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model="form.target_customer"
                      type="text"
                      required
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      권장 판매가 (원) <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.recommended_price"
                      type="number"
                      required
                      min="0"
                      step="1000"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                      수익률 (%) <span class="text-red-500">*</span>
                    </label>
                    <input
                      v-model.number="form.profit_margin"
                      type="number"
                      required
                      min="0"
                      max="100"
                      step="0.01"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    판매 포인트 (한 줄에 하나씩) <span class="text-red-500">*</span>
                  </label>
                  <textarea
                    v-model="sellingPointsText"
                    rows="5"
                    required
                    placeholder="각 판매 포인트를 한 줄에 하나씩 입력하세요"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                  <p class="mt-1 text-sm text-gray-500">예: 자동 물주기 시스템으로 편리한 관리</p>
                </div>
              </div>

              <!-- Tab: 원가 분석 -->
              <div v-if="activeTab === 'cost'" class="space-y-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">상품 단가 (원)</label>
                    <input
                      v-model.number="form.cost.product_unit_price"
                      type="number"
                      min="0"
                      step="100"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">국제 배송비 (원)</label>
                    <input
                      v-model.number="form.cost.international_shipping"
                      type="number"
                      min="0"
                      step="100"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">관세 (원)</label>
                    <input
                      v-model.number="form.cost.customs_duty"
                      type="number"
                      min="0"
                      step="100"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">국내 배송비 (원)</label>
                    <input
                      v-model.number="form.cost.domestic_shipping"
                      type="number"
                      min="0"
                      step="100"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    총 원가 (원) <span class="text-red-500">*</span>
                  </label>
                  <input
                    v-model.number="form.cost.total_cost"
                    type="number"
                    required
                    min="0"
                    step="100"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">공급처명</label>
                    <input
                      v-model="form.cost.supplier_name"
                      type="text"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>

                  <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">공급처 URL</label>
                    <input
                      v-model="form.cost.supplier_url"
                      type="url"
                      placeholder="https://"
                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    />
                  </div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">원가 메모</label>
                  <textarea
                    v-model="form.cost.cost_notes"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Tab: 콘텐츠 -->
              <div v-if="activeTab === 'content'" class="space-y-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">SEO 제목</label>
                  <input
                    v-model="form.content.seo_title"
                    type="text"
                    maxlength="255"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">헤더 문구</label>
                  <input
                    v-model="form.content.header_copy"
                    type="text"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">주요 특징</label>
                  <textarea
                    v-model="form.content.key_features"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">상세 설명</label>
                  <textarea
                    v-model="form.content.full_description"
                    rows="6"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">사용 방법</label>
                  <textarea
                    v-model="form.content.usage_guide"
                    rows="4"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">주의사항</label>
                  <textarea
                    v-model="form.content.precautions"
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">추천 대상</label>
                  <textarea
                    v-model="form.content.recommendation"
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  />
                </div>
              </div>

              <!-- Tab: 광고 문구 -->
              <div v-if="activeTab === 'ads'" class="space-y-8">
                <!-- Naver Ads -->
                <div>
                  <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                    <span class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white mr-3">
                      N
                    </span>
                    네이버 광고 문구
                  </h3>
                  <div class="space-y-4">
                    <div
                      v-for="(ad, index) in getAdsByPlatform('naver')"
                      :key="index"
                      class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                    >
                      <div class="text-xs text-gray-500 mb-3">버전 {{ ad.variation }}</div>
                      <div class="space-y-3">
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">헤드라인</label>
                          <input
                            v-model="ad.headline"
                            type="text"
                            maxlength="255"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">설명</label>
                          <textarea
                            v-model="ad.description"
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </div>
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
                  <div class="space-y-4">
                    <div
                      v-for="(ad, index) in getAdsByPlatform('google')"
                      :key="index"
                      class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                    >
                      <div class="text-xs text-gray-500 mb-3">버전 {{ ad.variation }}</div>
                      <div class="space-y-3">
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">헤드라인</label>
                          <input
                            v-model="ad.headline"
                            type="text"
                            maxlength="255"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">설명</label>
                          <textarea
                            v-model="ad.description"
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </div>
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
                  <div class="space-y-4">
                    <div
                      v-for="(ad, index) in getAdsByPlatform('meta')"
                      :key="index"
                      class="bg-gray-50 p-4 rounded-lg border border-gray-200"
                    >
                      <div class="text-xs text-gray-500 mb-3">버전 {{ ad.variation }}</div>
                      <div class="space-y-3">
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">헤드라인</label>
                          <input
                            v-model="ad.headline"
                            type="text"
                            maxlength="255"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                        <div>
                          <label class="block text-xs font-medium text-gray-700 mb-1">설명</label>
                          <textarea
                            v-model="ad.description"
                            rows="2"
                            class="w-full px-3 py-2 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500"
                          />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Action Buttons -->
          <div class="flex justify-end space-x-4">
            <Link
              :href="`/admin/products/${product.id}`"
              class="px-6 py-3 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition"
            >
              취소
            </Link>
            <button
              type="submit"
              :disabled="processing"
              class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {{ processing ? '저장 중...' : '저장' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue';
import { router, Link } from '@inertiajs/vue3';
import AppLayout from '@/Components/AppLayout.vue';

const props = defineProps({
  product: Object,
});

const activeTab = ref('basic');
const processing = ref(false);

const tabs = [
  { id: 'basic', name: '기본 정보' },
  { id: 'cost', name: '원가 분석' },
  { id: 'content', name: '콘텐츠' },
  { id: 'ads', name: '광고 문구' },
];

// Parse selling points from JSON to text
const parseSellingPoints = (points) => {
  try {
    const parsed = JSON.parse(points);
    return Array.isArray(parsed) ? parsed.join('\n') : '';
  } catch {
    return '';
  }
};

const sellingPointsText = ref(parseSellingPoints(props.product.selling_points));

const form = reactive({
  name: props.product.name,
  category: props.product.category,
  target_customer: props.product.target_customer,
  selling_points: props.product.selling_points,
  recommended_price: props.product.recommended_price,
  profit_margin: props.product.profit_margin,

  cost: {
    product_unit_price: props.product.cost?.product_unit_price || null,
    international_shipping: props.product.cost?.international_shipping || 0,
    customs_duty: props.product.cost?.customs_duty || 0,
    domestic_shipping: props.product.cost?.domestic_shipping || 0,
    risk_cost: props.product.cost?.risk_cost || 0,
    total_cost: props.product.cost?.total_cost || 0,
    supplier_name: props.product.cost?.supplier_name || '',
    supplier_url: props.product.cost?.supplier_url || '',
    cost_notes: props.product.cost?.cost_notes || '',
  },

  content: {
    seo_title: props.product.content?.seo_title || '',
    header_copy: props.product.content?.header_copy || '',
    key_features: props.product.content?.key_features || '',
    full_description: props.product.content?.full_description || '',
    usage_guide: props.product.content?.usage_guide || '',
    precautions: props.product.content?.precautions || '',
    recommendation: props.product.content?.recommendation || '',
  },

  ad_copies: props.product.ad_copies.map(ad => ({
    id: ad.id,
    platform: ad.platform,
    headline: ad.headline,
    description: ad.description,
    variation: ad.variation,
  })),
});

const getAdsByPlatform = (platform) => {
  return form.ad_copies.filter((ad) => ad.platform === platform);
};

const submitForm = () => {
  processing.value = true;

  // Convert selling points text to JSON array
  const sellingPointsArray = sellingPointsText.value
    .split('\n')
    .map(line => line.trim())
    .filter(line => line.length > 0);

  form.selling_points = JSON.stringify(sellingPointsArray);

  router.put(`/admin/products/${props.product.id}`, form, {
    onSuccess: () => {
      processing.value = false;
    },
    onError: (errors) => {
      processing.value = false;
      console.error('Validation errors:', errors);
      alert('저장 중 오류가 발생했습니다. 입력 내용을 확인해주세요.');
    },
  });
};
</script>
