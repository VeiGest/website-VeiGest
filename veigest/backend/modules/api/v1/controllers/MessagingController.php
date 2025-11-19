<?php

namespace backend\modules\api\v1\controllers;

use Yii;
use yii\filters\Cors;
use yii\web\Controller;
use yii\web\Response;

/**
 * Messaging Controller
 * 
 * Provides Server-Sent Events for real-time updates
 * 
 * @author VeiGest Team
 */
class MessagingController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();

        // CORS filter
        $behaviors['corsFilter'] = [
            'class' => Cors::class,
            'cors' => [
                'Origin' => ['*'],
                'Access-Control-Request-Method' => ['GET', 'POST'],
                'Access-Control-Request-Headers' => ['*'],
                'Access-Control-Allow-Credentials' => false,
                'Access-Control-Max-Age' => 86400,
            ]
        ];

        return $behaviors;
    }

    /**
     * Server-Sent Events endpoint for real-time updates
     */
    public function actionEvents()
    {
        // Set headers for Server-Sent Events
        Yii::$app->response->headers->set('Content-Type', 'text/event-stream');
        Yii::$app->response->headers->set('Cache-Control', 'no-cache');
        Yii::$app->response->headers->set('Connection', 'keep-alive');
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Send initial connection message
        echo "data: " . json_encode([
            'type' => 'connected',
            'message' => 'Connected to VeiGest real-time updates',
            'timestamp' => date('c')
        ]) . "\n\n";
        flush();
        
        // Keep connection alive and send periodic updates
        $startTime = time();
        $lastCheck = time();
        
        while (time() - $startTime < 300) { // 5 minutes timeout
            if (connection_aborted()) {
                break;
            }
            
            // Check for updates every 10 seconds
            if (time() - $lastCheck >= 10) {
                $this->sendSystemUpdate();
                $lastCheck = time();
            }
            
            // Send heartbeat every 30 seconds
            if ((time() - $startTime) % 30 === 0) {
                echo "data: " . json_encode([
                    'type' => 'heartbeat',
                    'timestamp' => date('c')
                ]) . "\n\n";
                flush();
            }
            
            sleep(1);
        }
        
        // Connection timeout
        echo "data: " . json_encode([
            'type' => 'timeout',
            'message' => 'Connection timeout',
            'timestamp' => date('c')
        ]) . "\n\n";
        flush();
    }

    /**
     * Send system updates
     */
    private function sendSystemUpdate()
    {
        $updates = $this->getSystemUpdates();
        
        if (!empty($updates)) {
            foreach ($updates as $update) {
                echo "data: " . json_encode($update) . "\n\n";
            }
            flush();
        }
    }

    /**
     * Get system updates (vehicles, maintenances, etc.)
     */
    private function getSystemUpdates()
    {
        $updates = [];
        
        // Check for recent vehicles (last 5 minutes)
        $recentVehicles = \backend\modules\api\v1\models\Vehicle::find()
            ->where(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-5 minutes'))])
            ->limit(5)
            ->all();
            
        foreach ($recentVehicles as $vehicle) {
            $updates[] = [
                'type' => 'vehicle_created',
                'data' => [
                    'id' => $vehicle->id,
                    'matricula' => $vehicle->matricula,
                    'marca' => $vehicle->marca,
                    'modelo' => $vehicle->modelo,
                ],
                'timestamp' => date('c')
            ];
        }
        
        // Check for recent maintenances
        $recentMaintenances = \backend\modules\api\v1\models\Maintenance::find()
            ->where(['>=', 'created_at', date('Y-m-d H:i:s', strtotime('-5 minutes'))])
            ->limit(5)
            ->all();
            
        foreach ($recentMaintenances as $maintenance) {
            $updates[] = [
                'type' => 'maintenance_created',
                'data' => [
                    'id' => $maintenance->id,
                    'vehicle_id' => $maintenance->vehicle_id,
                    'tipo' => $maintenance->tipo,
                    'estado' => $maintenance->estado,
                ],
                'timestamp' => date('c')
            ];
        }
        
        return $updates;
    }

    /**
     * Publish message to specific channel
     */
    public function actionPublish()
    {
        $request = Yii::$app->request;
        $channel = $request->post('channel');
        $message = $request->post('message');
        $data = $request->post('data', []);
        
        if (!$channel || !$message) {
            return [
                'success' => false,
                'message' => 'Channel and message are required'
            ];
        }
        
        // Store message in cache for SSE to pick up
        $messageData = [
            'type' => 'channel_message',
            'channel' => $channel,
            'message' => $message,
            'data' => $data,
            'timestamp' => date('c')
        ];
        
        Yii::$app->cache->set("message_{$channel}_" . time(), $messageData, 60);
        
        return [
            'success' => true,
            'message' => 'Message published successfully',
            'data' => $messageData
        ];
    }

    /**
     * Subscribe to specific channels
     */
    public function actionSubscribe($channels = '')
    {
        // Set headers for Server-Sent Events
        Yii::$app->response->headers->set('Content-Type', 'text/event-stream');
        Yii::$app->response->headers->set('Cache-Control', 'no-cache');
        Yii::$app->response->headers->set('Connection', 'keep-alive');
        
        $channelList = array_filter(explode(',', $channels));
        
        if (empty($channelList)) {
            $channelList = ['general', 'vehicles', 'maintenances'];
        }
        
        // Disable output buffering
        if (ob_get_level()) {
            ob_end_clean();
        }
        
        // Send subscription confirmation
        echo "data: " . json_encode([
            'type' => 'subscribed',
            'channels' => $channelList,
            'timestamp' => date('c')
        ]) . "\n\n";
        flush();
        
        $startTime = time();
        
        while (time() - $startTime < 300) { // 5 minutes timeout
            if (connection_aborted()) {
                break;
            }
            
            // Check for messages in subscribed channels
            foreach ($channelList as $channel) {
                $this->checkChannelMessages($channel);
            }
            
            sleep(2);
        }
    }

    /**
     * Check for messages in a specific channel
     */
    private function checkChannelMessages($channel)
    {
        $cacheKeys = Yii::$app->cache->getValues(["message_{$channel}_*"]);
        
        foreach ($cacheKeys as $key => $message) {
            if ($message) {
                echo "data: " . json_encode($message) . "\n\n";
                flush();
                
                // Remove message after sending
                Yii::$app->cache->delete($key);
            }
        }
    }

    /**
     * Get messaging statistics
     */
    public function actionStats()
    {
        return [
            'success' => true,
            'data' => [
                'active_connections' => 0, // Would need to track in production
                'total_messages' => 0,     // Would need to track in production
                'channels' => [
                    'general' => 'General system messages',
                    'vehicles' => 'Vehicle-related updates',
                    'maintenances' => 'Maintenance notifications',
                    'alerts' => 'System alerts and warnings',
                ],
                'endpoints' => [
                    'events' => '/api/v1/messaging/events',
                    'subscribe' => '/api/v1/messaging/subscribe?channels=general,vehicles',
                    'publish' => '/api/v1/messaging/publish',
                ]
            ]
        ];
    }
}