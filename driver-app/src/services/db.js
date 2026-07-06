import { openDB } from 'idb';

const DB_NAME = 'grease-cycling-offline';
const DB_VERSION = 1;

export const initDB = async () => {
  return openDB(DB_NAME, DB_VERSION, {
    upgrade(db) {
      // Store for pending offline pickup logs
      if (!db.objectStoreNames.contains('pickup_queue')) {
        db.createObjectStore('pickup_queue', { keyPath: 'id', autoIncrement: true });
      }
      // Store for cached route/stops list
      if (!db.objectStoreNames.contains('cached_route')) {
        db.createObjectStore('cached_route');
      }
    },
  });
};

export const getCachedRoute = async () => {
  try {
    const db = await initDB();
    return await db.get('cached_route', 'today_route');
  } catch (error) {
    console.error('IndexedDB getCachedRoute error:', error);
    return null;
  }
};

export const cacheRoute = async (routeData) => {
  try {
    const db = await initDB();
    return await db.put('cached_route', routeData, 'today_route');
  } catch (error) {
    console.error('IndexedDB cacheRoute error:', error);
  }
};

export const clearCachedRoute = async () => {
  try {
    const db = await initDB();
    return await db.delete('cached_route', 'today_route');
  } catch (error) {
    console.error('IndexedDB clearCachedRoute error:', error);
  }
};

export const queuePickup = async (stopId, payload) => {
  try {
    const db = await initDB();
    return await db.add('pickup_queue', {
      stopId,
      payload,
      timestamp: new Date().toISOString(),
    });
  } catch (error) {
    console.error('IndexedDB queuePickup error:', error);
    throw error;
  }
};

export const getQueuedPickups = async () => {
  try {
    const db = await initDB();
    return await db.getAll('pickup_queue');
  } catch (error) {
    console.error('IndexedDB getQueuedPickups error:', error);
    return [];
  }
};

export const deleteQueuedPickup = async (id) => {
  try {
    const db = await initDB();
    return await db.delete('pickup_queue', id);
  } catch (error) {
    console.error('IndexedDB deleteQueuedPickup error:', error);
  }
};
