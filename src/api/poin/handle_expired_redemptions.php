<?php
require_once __DIR__ . '/../../../aa_kon_sett.php';

// Function to handle expired redemptions
function handle_expired_redemptions()
{
    global $conn;

    try {
        // Query to find expired redemptions
        $stmt = $conn->prepare("SELECT ht.id_hadiah, ht.id_user, ht.qr_code_url, ua.no_hp, ht.expired_at FROM hadiah_t ht
        LEFT JOIN user_asoka ua ON ht.id_user = ua.id_user
        WHERE STATUS = 'pending'");
        if (!$stmt) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'query_prepare_failed',
                'message' => 'Query prepare failed',
                'detail' => $conn->error
            ]);
            return;
        }
        if (!$stmt->execute()) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'query_execute_failed',
                'message' => 'Query execute failed',
                'detail' => $stmt->error
            ]);
            return;
        }
        $result = $stmt->get_result();

        $processed = 0;
        $errors = [];

        while ($row = $result->fetch_assoc()) {
            $reward_id = $row['id_hadiah'];
            $user_id = $row['id_user'];
            $user_phone = $row['no_hp'];
            $redemption_code = $row['qr_code_url'];
            $expired_at = $row['expired_at'];

            // Check if the redemption is already expired
            if (strtotime($expired_at) > time()) {
                continue;
            } 

            // Update the status to expired
            $update_stmt = $conn->prepare("UPDATE hadiah_t SET status = 'expired' WHERE qr_code_url = ? AND id_user = ? AND id_hadiah = ?");
            if (!$update_stmt) {
                $errors[] = [
                    'step' => 'update_hadiah_t_prepare',
                    'reward_id' => $reward_id,
                    'error' => $conn->error
                ];
                continue;
            }
            $update_stmt->bind_param('sii', $redemption_code, $user_id, $reward_id);
            if (!$update_stmt->execute()) {
                $errors[] = [
                    'step' => 'update_hadiah_t_execute',
                    'reward_id' => $reward_id,
                    'error' => $update_stmt->error
                ];
                continue;
            }

            // Restore reward quantity
            $restore_reward_stmt = $conn->prepare("UPDATE hadiah SET qty = qty + 1 WHERE id_hadiah = ?");
            if (!$restore_reward_stmt) {
                $errors[] = [
                    'step' => 'restore_hadiah_prepare',
                    'reward_id' => $reward_id,
                    'error' => $conn->error
                ];
                continue;
            }
            $restore_reward_stmt->bind_param('i', $reward_id);
            if (!$restore_reward_stmt->execute()) {
                $errors[] = [
                    'step' => 'restore_hadiah_execute',
                    'reward_id' => $reward_id,
                    'error' => $restore_reward_stmt->error
                ];
                continue;
            }

            // Delete the associated point transaction
            $delete_points_stmt = $conn->prepare("DELETE FROM point_trans WHERE kd_cust = ? AND no_trans = ?");
            if (!$delete_points_stmt) {
                $errors[] = [
                    'step' => 'delete_poin_trans_prepare',
                    'reward_id' => $reward_id,
                    'error' => $conn->error
                ];
                continue;
            }
            $delete_points_stmt->bind_param('ss', $user_phone, $redemption_code);
            if (!$delete_points_stmt->execute()) {
                $errors[] = [
                    'step' => 'delete_poin_trans_execute',
                    'reward_id' => $reward_id,
                    'error' => $delete_points_stmt->error
                ];
                continue;
            }

            $processed++;
        }

        if (count($errors) > 0) {
            http_response_code(207); // Multi-Status
            echo json_encode([
                'success' => false,
                'message' => 'Some expired redemptions failed to process',
                'processed' => $processed,
                'errors' => $errors
            ]);
        } else {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'message' => 'Expired redemptions handled successfully',
                'processed' => $processed
            ]);
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'exception',
            'message' => 'Error occurred',
            'detail' => $e->getMessage()
        ]);
    }
}

// Execute the function
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    handle_expired_redemptions();
} else {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
}
