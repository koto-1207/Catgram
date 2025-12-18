<?php

use function Livewire\Volt\state;
use function Livewire\Volt\usesFileUploads;
use App\Models\Cat;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

state([
    // ネコ一覧
    'cats' => fn() => Cat::latest()->get(),

    // アップロード画像
    'photo',
]);

// このコンポーネントでファイルアップロードを有効化
usesFileUploads();

// 画像投稿
$save = function () {
    $this->validate(
        [
            // 5MBまでの「ブラウザで表示できる一般的な画像形式」のみ許可
            'photo' => 'required|image|max:5120',
        ],
        [
            'photo.required' => 'ねこの写真を選択してください。',
            'photo.image' => '対応している画像形式は jpeg / jpg / png / gif / webp です。',
            'photo.max' => '画像サイズは5MB以下にしてください。',
        ],
    );

    /** @var UploadedFile $photo */
    $photo = $this->photo;
    $path = $photo->store('cats', 'public');

    Cat::create([
        'image_path' => $path,
    ]);

    // 再読み込み
    $this->photo = null;
    $this->cats = Cat::latest()->get();
};

// いいね処理
$like = function ($id) {
    $cat = Cat::findOrFail($id);
    $cat->increment('likes');

    $this->cats = Cat::latest()->get();
};

// 削除処理
$delete = function ($id) {
    $cat = Cat::findOrFail($id);

    // 画像ファイルも削除（存在しない場合は何も起きない）
    if ($cat->image_path) {
        Storage::disk('public')->delete($cat->image_path);
    }

    $cat->delete();

    // 一覧を再読込
    $this->cats = Cat::latest()->get();
};

?>

<div class="max-w-5xl mx-auto p-6 space-y-8">
    <header class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-2">
        <div>
            <h1 class="text-3xl font-bold flex items-center gap-2">
                🐱 ねこ自慢アプリ
            </h1>
            <p class="text-sm text-gray-500 mt-1">
                かわいいねこの写真を投稿して、みんなの「いいね」をあつめましょう。
            </p>
        </div>
    </header>

    {{-- 投稿フォーム --}}
    <section class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 space-y-4">
        <h2 class="text-lg font-semibold">写真を投稿する</h2>

        <form wire:submit.prevent="save" class="space-y-4">
            <div class="space-y-1">
                <label for="photo" class="block text-sm font-medium text-gray-700">
                    ねこの写真
                </label>
                <input id="photo" type="file" wire:model="photo"
                    class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                    accept="image/jpeg,image/png,image/gif,image/webp">
                @error('photo')
                    <div class="text-red-500 text-sm mt-1">{{ $message }}</div>
                @enderror

                {{-- アップロード中の表示 --}}
                <div wire:loading wire:target="photo" class="text-xs text-gray-500 mt-1">
                    画像を読み込み中です…
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 disabled:opacity-60 disabled:cursor-not-allowed transition"
                    wire:loading.attr="disabled" wire:target="save,photo">
                    <span wire:loading.remove wire:target="save">🐾 投稿する</span>
                    <span wire:loading wire:target="save">送信中…</span>
                </button>

                <p class="text-xs text-gray-500">
                    5MBまでの画像ファイル（JPEG / PNG / GIF / WebP）をアップロードできます。
                </p>
            </div>
        </form>
    </section>

    {{-- 一覧 --}}
    <section class="space-y-3">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold">みんなのねこ</h2>
            <p class="text-xs text-gray-500">
                全{{ $cats->count() }}件
            </p>
        </div>

        @if ($cats->isEmpty())
            <div
                class="border border-dashed border-gray-300 rounded-lg p-8 text-center text-gray-500 text-sm bg-gray-50">
                まだ投稿がありません。最初の「ねこ自慢」を投稿してみましょう！
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
                @foreach ($cats as $cat)
                    <div class="bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm flex flex-col">
                        <img src="{{ asset('storage/' . $cat->image_path) }}" class="w-full h-60 object-cover"
                            alt="ねこの写真">

                        <div class="flex items-center justify-between px-3 py-2">
                            <div class="flex items-center gap-3">
                                <button type="button" wire:click="like({{ $cat->id }})"
                                    class="inline-flex items-center gap-1 text-pink-600 hover:text-pink-700 text-sm font-medium">
                                    ❤️
                                    <span>{{ $cat->likes }}</span>
                                </button>

                                <button type="button" wire:click="delete({{ $cat->id }})"
                                    class="text-xs text-gray-400 hover:text-red-600">
                                    削除
                                </button>
                            </div>

                            <span class="text-[11px] text-gray-400">
                                ID: {{ $cat->id }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </section>
</div>
