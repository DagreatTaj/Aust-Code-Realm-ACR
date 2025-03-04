<?php
session_start();

if (!isset($_SESSION['user']['UserID'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user']['UserID'];

include '../helpers/config.php';

// Fetch all submissions initially
$query = "SELECT submissions.*, problems.Name AS ProblemName, users.Handle AS UserHandle
          FROM submissions
          LEFT JOIN problems ON submissions.ProblemID = problems.ProblemID
          LEFT JOIN users ON submissions.UserID = users.UserID";
$result = $conn->query($query);

$submissions = [];
while ($row = $result->fetch_assoc()) {
    $submissions[] = $row;
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/bootstrap.min.css">
    <link rel="stylesheet" href="../css/navbar.css">
    <link rel="stylesheet" href="../css/submission.css">
    <title>My Submissions - AUST CODE REALM</title>
</head>
<body>
    <!-- Navbar -->
    <?php include '../helpers/navbar.php'; ?>

    <div class="container mt-5">
        <h1 class="text-center mb-4">Submissions</h1>
        
        <!-- Filter Options -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="searchUsername" class="form-control" placeholder="Search by username...">
                <input type="checkbox" id="showAll" class="form-check-input">
                <label for="showAll" class="form-check-label">Show All Users Submissions</label>
            </div>
            <div class="col-md-4">
                <input type="text" id="searchProblem" class="form-control" placeholder="Search by problem name...">
            </div>
            <div class="col-md-3">
                <select id="statusFilter" class="form-control">
                    <option value="">All Statuses</option>
                    <option value="Accepted">Accepted</option>
                    <option value="Rejected">Wrong Answer</option>
                    <option value="Pending">Time Limit Exceeded</option>
                </select>
            </div>
            <div class="col-md-1">
                <button class="btn btn-primary float-end" id="ClearButton">Clear</button>
            </div>
        </div>
        
        <table class="table table-striped">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Problem</th>
                    <th scope="col">User</th>
                    <th scope="col">Status</th>
                    <th scope="col">Submission Time</th>
                    <th scope="col">Time Taken</th>
                    <th scope="col">Memory</th>
                    <th scope="col">Language</th>
                </tr>
            </thead>
            <tbody id="submissionsTableBody">
                <?php foreach ($submissions as $submission): ?>
                    <tr>
                        <th scope="row">
                            <a href="#" class="submission-id" data-code="<?php echo htmlspecialchars($submission['Code']); ?>" data-lang="<?php echo htmlspecialchars($submission['LanguageID']); ?>">
                                <?php echo htmlspecialchars($submission['SubmissionID']); ?>
                            </a>
                        </th>
                        <td><a href="problemPage.php?id=<?php echo $submission['ProblemID']; ?>"><?php echo $submission['ProblemName']; ?></a></td>
                        <td><?php echo $submission['UserHandle']; ?></td>
                        <td><?php echo $submission['Status']; ?></td>
                        <td><?php echo $submission['SubmissionTime']; ?></td>
                        <td><?php echo htmlspecialchars($submission['TimeTaken']); ?></td>
                        <td><?php echo htmlspecialchars($submission['MemoryUsed']); ?></td>
                        <td><?php echo htmlspecialchars($submission['LanguageID']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Code Modal -->
    <div class="modal fade" id="codeModal" tabindex="-1" aria-labelledby="codeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="codeModalLabel">Submission Code</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <textarea id="codeTextarea" class="form-control" rows="15" readonly></textarea>
                    <button class="btn btn-primary mt-3" id="copyButton">Copy Code</button>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/jquery-3.1.1.min.js"></script>
    <script>
        $(document).ready(function() {
            function fetchSubmissions() {
                let searchUsername = $('#searchUsername').val();
                let searchProblem = $('#searchProblem').val();
                let statusFilter = $('#statusFilter').val();
                let showAll = $('#showAll').is(':checked') ? 1 : 0;

                $.ajax({
                    url: '../helpers/fetchSubmissions.php',
                    type: 'GET',
                    data: {
                        searchUsername: searchUsername,
                        searchProblem: searchProblem,
                        statusFilter: statusFilter,
                        showAll: showAll
                    },
                    success: function(response) {
                        $('#submissionsTableBody').html(response);
                        bindSubmissionIdClick();
                    }
                });
            }

            function bindSubmissionIdClick() {
                $('.submission-id').on('click', function(event) {
                    event.preventDefault();
                    const code = $(this).data('code');
                    $('#codeTextarea').val(code);
                    const modal = new bootstrap.Modal(document.getElementById('codeModal'));
                    modal.show();
                });
            }

            // Initial fetch
            fetchSubmissions();

            // Filter event handlers
            $('#searchUsername, #searchProblem, #statusFilter, #showAll').on('change', function() {
                fetchSubmissions();
            });

            $('#ClearButton').on('click', function() {
                $('#searchUsername').val('');
                $('#searchProblem').val('');
                $('#statusFilter').val('');
                fetchSubmissions();
            });

            // Bind click event to copy button
            $('#copyButton').on('click', function() {
                const codeTextarea = $('#codeTextarea');
                codeTextarea.select();
                document.execCommand('copy');
            });

            // Bind click events to submission IDs initially
            bindSubmissionIdClick();
        });
    </script>
    <script src="../js/bootstrap.bundle.min.js"></script>
</body>
</html>
