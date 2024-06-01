<!DOCTYPE html>
<html>

<head>
    <title>Product Description Analyser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @if (isset($descriptions))
    <script>
        window.descriptions = @js(
            array_reduce(
                $descriptions,
                function ($carry, $descr) {
                    $carry[$descr['id']] = $descr;
                    return $carry;
                },
                []
            )
)
    </script>
    @endif
</head>

<body>
    <div class="container mt-5">
        <h1>Product Description Sentiment Analyser</h1>
        <br />
        <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="formFile" class="form-label">Upload CSV file</label>
                <input class="form-control" type="file" id="formFile" name="file">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
            <button type="submit" formaction="{{ route('re-analyse') }}" class="btn btn-secondary">Force
                re-analyse</button>
                <button type="submit" formaction="{{ route('file-unselect') }}" class="btn btn-secondary">Deselect file</button>
        </form>
        <!-- TODO: Add here resolver for minimal and maximal score from descriptions array -->
        @php
            if ($descriptions) {
                $scores = array_column($descriptions, 'score');

                $allNumeric = array_reduce($scores, fn($carry, $score) => $carry && is_numeric($score), true);

                if ($allNumeric) {
                    $validScores = array_filter($scores, fn($score) => is_numeric($score));
                    if ($validScores) {
                        $minScore = min($validScores);
                        $maxScore = max($validScores);
                        $minScoreHash = array_search($minScore, array_column($descriptions, 'score'));
                        $maxScoreHash = array_search($maxScore, array_column($descriptions, 'score'));
                    }
                }
            } else {
                $allNumeric = false;
            }
        @endphp
        @if ($filename)
            <div id="dashboard" class="mt-5">
                <h3>Current file: {{ $filename }}</h3>
                <h2>Results: </h2>
                <div id="results-content">
                    @if ($allNumeric)
                        <h2>Minimal score: <a href="#{{ $descriptions[$minScoreHash]['hash'] }}">{{ $minScore }}</a>
                        </h2>
                        <h2>Maximal score: <a href="#{{ $descriptions[$maxScoreHash]['hash'] }}">{{ $maxScore }}</a>
                        </h2>
                    @else
                        <div id="dashboard-loader" class="loader"></div>
                    @endif
                </div>
            </div>
        @endif

        <table class="table mt-5">
            <thead>
                <tr>
                    <th scope="col">Product Name</th>
                    <th scope="col">Description</th>
                    <th scope="col">Score</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($descriptions as $description)
                                @php
                                    $row_class = '';
                                    if (isset($description['score']) && is_numeric($description['score'])) {
                                        if ($description['score'] > 0.5)
                                            $row_class = 'table-success';
                                        else if ($description['score'] < 0.5)
                                            $row_class = 'table-danger';
                                        else
                                            $row_class = 'table-warning';
                                    }
                                @endphp
                                <tr class="{{ $row_class }}" id="{{ $description['hash'] }}">
                                    <td>{{ $description['name'] }}</td>
                                    <td>{{ $description['description'] }}</td>
                                    <td class="score">
                                        @if (is_null($description['score']))
                                            <div class="loader"></div>
                                        @endif
                                        {{ $description['score'] ?? '' }}

                                    </td>
                                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
