import { getQueuedPickups, deleteQueuedPickup } from './db';
import axios from 'axios';

const apiBaseUrl = import.meta.env.VITE_API_BASE_URL || '/api';

/**
 * Sync all queued offline pickups to the backend.
 * Returns the number of successfully synced items.
 */
export const syncOfflinePickups = async () => {
  const queued = await getQueuedPickups();
  if (queued.length === 0) return 0;

  let successCount = 0;

  for (const item of queued) {
    try {
      await axios.post(`${apiBaseUrl}/driver/stops/${item.stopId}/pickup`, item.payload);
      await deleteQueuedPickup(item.id);
      successCount++;
    } catch (error) {
      console.error(`Sync failed for stop #${item.stopId}:`, error);
      
      // If it's a connection/network timeout error (no response),
      // we stop syncing the rest to avoid spamming while connection is weak.
      if (!error.response) {
        break; 
      }
      
      // If it's a client/validation error (400-499), delete it so it doesn't clog the queue.
      if (error.response.status >= 400 && error.response.status < 500) {
        await deleteQueuedPickup(item.id);
      }
    }
  }

  return successCount;
};
