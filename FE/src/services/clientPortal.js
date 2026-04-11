import { clientApiClient, unwrapResponse } from './api';

export async function fetchClientRoutes(params = {}) {
  const response = await clientApiClient.get('/client/routes', { params });
  return unwrapResponse(response);
}

export async function fetchClientRouteDetail(routeId) {
  const response = await clientApiClient.get(`/client/routes/${routeId}`);
  return unwrapResponse(response);
}

export async function fetchRouteGeometry(routeId) {
  const response = await clientApiClient.get(`/client/routes/${routeId}/geometry`);
  return unwrapResponse(response);
}

export async function recordClientRouteView(routeId) {
  const response = await clientApiClient.post(`/client/routes/${routeId}/record-view`);
  return unwrapResponse(response);
}

export async function recommendClientRoutes(params = {}) {
  const response = await clientApiClient.get('/client/routes/recommend', { params });
  return unwrapResponse(response);
}

export async function fetchNearbyStops(params = {}) {
  const response = await clientApiClient.get('/client/stops/nearby', { params });
  return unwrapResponse(response);
}

export async function fetchClientStopDetail(stopId) {
  const response = await clientApiClient.get(`/client/stops/${stopId}`);
  return unwrapResponse(response);
}

export async function fetchFavoriteRoutes(params = {}) {
  const response = await clientApiClient.get('/client/me/favorites', { params });
  return unwrapResponse(response);
}

export async function addFavoriteRoute(routeId) {
  const response = await clientApiClient.post(`/client/me/favorites/${routeId}`);
  return unwrapResponse(response);
}

export async function removeFavoriteRoute(routeId) {
  const response = await clientApiClient.delete(`/client/me/favorites/${routeId}`);
  return unwrapResponse(response);
}

export async function fetchSearchHistories(params = {}) {
  const response = await clientApiClient.get('/client/me/search-histories', { params });
  return unwrapResponse(response);
}

export async function saveSearchHistory(payload) {
  const response = await clientApiClient.post('/client/me/search-histories', payload);
  return unwrapResponse(response);
}

export async function removeSearchHistory(historyId) {
  const response = await clientApiClient.delete(`/client/me/search-histories/${historyId}`);
  return unwrapResponse(response);
}

export async function clearSearchHistories() {
  const response = await clientApiClient.delete('/client/me/search-histories');
  return unwrapResponse(response);
}

export async function fetchViewedRoutes(params = {}) {
  const response = await clientApiClient.get('/client/me/viewed-routes', { params });
  return unwrapResponse(response);
}

export async function fetchStopSuggestions(params = {}) {
  const response = await clientApiClient.get('/client/stops/suggestions', { params });
  return unwrapResponse(response);
}

export async function fetchReachableStops(stopId, params = {}) {
  const response = await clientApiClient.get(`/client/stops/reachable-from/${stopId}`, { params });
  return unwrapResponse(response);
}

export async function updateClientProfile(payload) {
  const response = await clientApiClient.put('/client/auth/profile', payload);
  return unwrapResponse(response);
}

export async function updateClientPassword(payload) {
  const response = await clientApiClient.put('/client/auth/password', payload);
  return unwrapResponse(response);
}

export async function fetchRouteBuses(routeId) {
  const response = await clientApiClient.get(`/client/routes/${routeId}/buses`);
  return unwrapResponse(response);
}

export async function fetchRouteSchedule(routeId) {
  const response = await clientApiClient.get(`/client/routes/${routeId}/schedule`);
  return unwrapResponse(response);
}

export async function fetchStopBuses(stopId) {
  const response = await clientApiClient.get(`/client/stops/${stopId}/buses`);
  return unwrapResponse(response);
}

export async function fetchLiveBuses() {
  const response = await clientApiClient.get('/client/buses/live');
  return unwrapResponse(response);
}
