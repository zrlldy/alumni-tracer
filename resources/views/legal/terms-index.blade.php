@foreach ($document as $index => $block)
    @if ($block['type'] === 'heading')
        <a href="#section-{{ $index }}">{{ $block['text'] }}</a>
    @endif
@endforeach
