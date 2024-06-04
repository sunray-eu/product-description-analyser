<!DOCTYPE html>
<html>

<head>
    <title>Product Description Analyser</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @vite('resources/js/app.js')
    @vite('resources/css/app.css')
    @if (isset($descriptions))
    <script>
        window.descriptions = @js(array_reduce($descriptions, function ($carry, $descr) {
            $carry[$descr['id']] = $descr;
            return $carry;
        }, []));
    </script>
    @endif
    <style>
        .zoom-animation {
            transition: transform 0.3s ease-in-out;
        }
        .zoom-animation.zoomed {
            transform: scale(1.1);
        }

        .alert {
            position: fixed;
            top: 10px;
            right: 10px;
            z-index: 1000;
            opacity: 1;
            transition: opacity 0.5s ease-in-out;
        }
        .alert.fade {
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
    </style>
</head>

<body>
    <div class="container mt-5">
        <h1>Product Description Sentiment Analyser</h1>
        <br />
        @if ($errors->any())
            <div class="alert alert-danger show" role="alert">
            <ul>
                @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
                @endforeach
            </ul>
            <script>
                setTimeout(function() {
                    document.querySelector('.alert').classList.add('fade');
                }, 5000);
            </script>
        </div>
        @endif
        <form action="{{ route('upload') }}" method="post" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
                <label for="formFile" class="form-label">Upload CSV file</label>
                <input class="form-control" type="file" id="formFile" name="file" {{ empty($fileName) ? 'required' : null }}>
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
            <button type="submit" formaction="{{ route('re-analyse') }}" class="btn btn-secondary">Force re-analyse</button>
            <button type="submit" formaction="{{ route('file-unselect') }}" class="btn btn-secondary">Deselect file</button>
        </form>

        @php
            $allNumeric = false;
            $scores = array_column($descriptions, 'score');
            $validScores = isset($descriptions) ? array_filter($scores, fn($score) => is_numeric($score)) : [];

            $scoresCount = isset($descriptions) && count($descriptions) > 0 ? count($descriptions) : 1;
            $validScoresCount = count($validScores);

            $completedPercentage = number_format(($validScoresCount / $scoresCount) * 100, 2);

            if (isset($descriptions) && count($descriptions) > 0) {
                $allNumeric = array_reduce($scores, fn($carry, $score) => $carry && is_numeric($score), true);
                if ($allNumeric) {
                    if ($validScoresCount > 0) {
                        $minScore = min($validScores);
                        $maxScore = max($validScores);
                        $minScoreHash = array_search($minScore, array_column($descriptions, 'score'));
                        $maxScoreHash = array_search($maxScore, array_column($descriptions, 'score'));
                    }
                }
            }
        @endphp

        @if (isset($filename))
            <div id="dashboard" class="mt-5">
                <h3>Current file: {{ $filename }}</h3>
                <h2>Results:</h2>
                <div id="results-content">
                    @if ($allNumeric)
                        <h2>Minimal score: <a href="#{{ $descriptions[$minScoreHash]['hash'] }}" class="score-link">{{ $minScore }}</a></h2>
                        <h2>Maximal score: <a href="#{{ $descriptions[$maxScoreHash]['hash'] }}" class="score-link">{{ $maxScore }}</a></h2>
                    @else
                        <div id="dashboard-loader">
                            <div class="loader mt-4"></div>
                            <div id="loading-progress" class="progress mt-4">
                                <div id="loading-progress-bar" class="progress-bar" role="progressbar" style="width: {{ $completedPercentage }}%" aria-valuenow="{{ $validScoresCount }}" aria-valuemin="0" aria-valuemax="{{ $scoresCount }}">{{ $completedPercentage }}%</div>
                            </div>
                        </div>
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
                            if ($description['score'] > 0.5) {
                                $row_class = 'table-success';
                            } elseif ($description['score'] < -0.5) {
                                $row_class = 'table-danger';
                            } else {
                                $row_class = 'table-warning';
                            }
                        }
                    @endphp
                    <tr class="{{ $row_class }} zoom-animation" id="{{ $description['hash'] }}">
                        <td>{{ $description['name'] }}</td>
                        <td>{{ $description['description'] }}</td>
                        <td class="score">
                            @if (is_null($description['score']))
                                <div class="loader"></div>
                            @else
                                {{ $description['score'] }}
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</body>

</html>
