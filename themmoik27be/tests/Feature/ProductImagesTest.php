<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\KhachHang;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\Support\IsolatedApiTestCase;

class ProductImagesTest extends IsolatedApiTestCase
{
    public function test_color_image_is_shared_by_sizes_and_rejects_images_of_other_products(): void
    {
        (require database_path('migrations/2026_11_04_000001_add_image_to_product_variants.php'))->up();
        $token = $this->token();
        $photo = $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()]], $token)->assertOk()->json('data.anh_san_phams.0.id');
        $color = DB::table('mau_sacs')->insertGetId(['ten_mau' => 'Xanh QA', 'ma_mau' => '#135790', 'trang_thai' => 1]);
        $size = DB::table('kich_thuocs')->insertGetId(['ten_kich_thuoc' => '42 QA', 'trang_thai' => 1]);
        $payload = ['id_san_pham' => 1, 'id_mau_sac' => $color, 'so_luong_ton' => 3, 'id_anh_san_pham' => $photo];
        $this->callApi('POST', 'admin/chi-tiet-san-pham/create', $payload, $token)->assertCreated();
        unset($payload['id_anh_san_pham']);
        $this->callApi('POST', 'admin/chi-tiet-san-pham/create', $payload + ['id_kich_thuoc' => $size], $token)->assertCreated();
        $this->assertSame(2, DB::table('chi_tiet_san_phams')->where('id_mau_sac', $color)->where('id_anh_san_pham', $photo)->count());
        $other = $this->callApi('POST', 'admin/san-pham/create', ['ten_san_pham' => 'Other image QA', 'id_danh_muc' => 1, 'gia_ban' => 20000], $token)->assertCreated()->json('data.id');
        $payload['id_san_pham'] = $other;
        $this->callApi('POST', 'admin/chi-tiet-san-pham/create', $payload + ['id_anh_san_pham' => $photo], $token)->assertUnprocessable();
        $this->callApi('DELETE', 'admin/san-pham/1/images/'.$photo, [], $token)->assertOk();
        $this->assertSame(2, DB::table('chi_tiet_san_phams')->where('id_mau_sac', $color)->whereNull('id_anh_san_pham')->count());
    }

    public function test_illustration_label_survives_upload_and_public_delivery(): void
    {
        $token = $this->token();
        $response = $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()], 'image_kind' => 'illustration'], $token)->assertOk();
        $url = $response->json('data.anh_san_phams.0.duong_dan_anh');
        $this->assertStringContainsString('/illustration-', $url);
        $this->get(parse_url($url, PHP_URL_PATH))->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()], 'image_kind' => 'unknown'], $token)->assertStatus(422);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function token(): string
    {
        return User::create(['name' => 'Image QA', 'email' => uniqid().'@example.test', 'password' => Hash::make('Isolated-471!')])->createToken('images')->plainTextToken;
    }

    private function callApi(string $method, string $url, array $data = [], ?string $token = null)
    {
        $this->app['auth']->forgetGuards();
        $headers = ['Accept' => 'application/json'];
        if ($token) $headers['Authorization'] = 'Bearer '.$token;
        return $this->call($method, '/api/'.$url, $data, [], [], $this->transformHeadersToServerVars($headers));
    }

    private function image(string $name = 'front.png'): UploadedFile
    {
        return UploadedFile::fake()->createWithContent($name, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jWZkAAAAASUVORK5CYII='));
    }

    public function test_admin_creates_hidden_product_uploads_orders_images_and_customer_receives_exact_content(): void
    {
        $token = $this->token();
        $created = $this->callApi('POST', 'admin/san-pham/create', ['ten_san_pham' => 'UAT image fixture', 'id_danh_muc' => 1, 'gia_ban' => 300000, 'gia_khuyen_mai' => 250000, 'mo_ta_ngan' => 'Mô tả ảnh thật', 'mo_ta_chi_tiet' => 'Chi tiết riêng fixture'], $token)->assertCreated();
        $id = $created->json('data.id'); $slug = $created->json('data.slug_san_pham');
        $this->getJson('/api/client/san-pham/'.$slug.'?presentation=2')->assertNotFound();
        $response = $this->callApi('POST', 'admin/san-pham/'.$id.'/images', ['images' => [$this->image(), $this->image('back.png')]], $token)->assertOk()->assertJsonCount(2, 'data.anh_san_phams');
        $photos = $response->json('data.anh_san_phams');
        $this->assertNotSame($photos[0]['duong_dan_anh'], $photos[1]['duong_dan_anh']);
        $this->assertSame($photos[0]['duong_dan_anh'], $response->json('data.hinh_anh'));
        $path = parse_url($photos[0]['duong_dan_anh'], PHP_URL_PATH);
        $this->get($path)->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff')->assertHeader('Content-Type', 'image/png');
        $this->callApi('POST', 'admin/san-pham/'.$id.'/images/order', ['ids' => [$photos[1]['id'], $photos[0]['id']], 'main_id' => $photos[1]['id']], $token)->assertOk()->assertJsonPath('data.hinh_anh', $photos[1]['duong_dan_anh']);
        $this->callApi('POST', 'admin/san-pham/change-status', ['id' => $id], $token)->assertOk();
        $this->getJson('/api/client/san-pham/'.$slug.'?presentation=2')->assertOk()
            ->assertJsonPath('data.san_pham.hinh_anh', $photos[1]['duong_dan_anh'])
            ->assertJsonPath('data.san_pham.anh_san_phams.0.id', $photos[1]['id'])
            ->assertJsonPath('data.san_pham.mo_ta_chi_tiet', 'Chi tiết riêng fixture');
        $this->getJson('/api/client/san-pham/'.$slug)->assertOk()->assertJsonPath('data.san_pham.hinh_anh', $photos[1]['duong_dan_anh']);
        $catalog = $this->getJson('/api/client/san-phams?all=1')->assertOk()->json('data');
        $item = collect($catalog)->firstWhere('id', $id);
        $this->assertSame($photos[1]['duong_dan_anh'], $item['hinh_anh']);
        $this->callApi('DELETE', 'admin/san-pham/'.$id.'/images/'.$photos[1]['id'], [], $token)->assertOk()->assertJsonCount(1, 'data.anh_san_phams')->assertJsonPath('data.hinh_anh', $photos[0]['duong_dan_anh']);
        // Ảnh lịch sử vẫn truy cập được sau khi gỡ liên kết.
        $this->get(parse_url($photos[1]['duong_dan_anh'], PHP_URL_PATH))->assertOk();
        $this->callApi('DELETE', 'admin/san-pham/'.$id.'/images/'.$photos[0]['id'], [], $token)->assertOk()->assertJsonPath('data.hinh_anh', null);
        $this->assertCount(2, Storage::disk('public')->allFiles());
    }

    public function test_upload_rejects_invalid_types_size_and_foreign_image_ids(): void
    {
        $token = $this->token();
        foreach (['script.php' => '<?php echo 1;', 'vector.svg' => '<svg xmlns="http://www.w3.org/2000/svg"></svg>', 'fake.jpg' => 'not an image'] as $name => $bytes) {
            $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image(), UploadedFile::fake()->createWithContent($name, $bytes)]], $token)->assertStatus(422);
        }
        $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [UploadedFile::fake()->create('huge.jpg', 3073, 'image/jpeg')]], $token)->assertStatus(422);
        $this->assertCount(0, Storage::disk('public')->allFiles());
        $first = $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()]], $token)->assertOk()->json('data.anh_san_phams.0');
        $other = $this->callApi('POST', 'admin/san-pham/2/images', ['images' => [$this->image()]], $token)->assertOk()->json('data.anh_san_phams.0');
        $this->callApi('POST', 'admin/san-pham/1/images/order', ['ids' => [$other['id']], 'main_id' => $other['id']], $token)->assertStatus(422);
        $this->callApi('POST', 'admin/san-pham/1/images/order', ['ids' => [$first['id']], 'main_id' => $other['id']], $token)->assertStatus(422);
        $this->callApi('POST', 'admin/san-pham/1/images/order', ['ids' => []], $token)->assertStatus(422);
        $this->callApi('DELETE', 'admin/san-pham/1/images/'.$other['id'], [], $token)->assertNotFound();
        $this->get('/api/product-images/1/script.php')->assertNotFound();
        $this->get('/api/product-images/1/00000000-0000-0000-0000-000000000000.png')->assertNotFound();
    }

    public function test_only_admin_can_write_product_images(): void
    {
        $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()]])->assertUnauthorized();
        foreach (['customer', 'seller'] as $role) {
            $user = KhachHang::create(['ho_va_ten' => 'QA '.$role, 'email' => $role.'@example.test', 'mat_khau' => Hash::make('Isolated-471!'), 'role' => $role, 'is_khoa' => 0, 'is_kich_hoat' => 1]);
            $token = $user->createToken('images')->plainTextToken;
            $this->callApi('POST', 'admin/san-pham/1/images', ['images' => [$this->image()]], $token)->assertForbidden();
            $this->callApi('POST', 'admin/san-pham/1/images/order', ['ids' => []], $token)->assertForbidden();
            $this->callApi('DELETE', 'admin/san-pham/1/images/1', [], $token)->assertForbidden();
        }
        $this->assertCount(0, Storage::disk('public')->allFiles());
    }
}
