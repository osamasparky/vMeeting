import { OfficeUser } from '../events/event-types.js';

export class ProximityCalculator {
  private defaultAudibleRadius: number;

  constructor(defaultAudibleRadius: number = 150) {
    this.defaultAudibleRadius = defaultAudibleRadius;
  }

  /**
   * Calculate proximity neighbors for a user on a map.
   * Enforces private room isolation (users in a private room only hear room occupants).
   */
  public getNearbyUsers(currentUser: OfficeUser, allUsers: OfficeUser[]): string[] {
    const nearby: string[] = [];

    // If current user is in a private room, they only hear occupants in that exact room
    if (currentUser.currentRoomId) {
      return allUsers
        .filter(
          (u) =>
            u.userId !== currentUser.userId &&
            u.currentRoomId === currentUser.currentRoomId
        )
        .map((u) => u.userId);
    }

    // For open office / zones: calculate euclidean distance between avatar positions
    const radius = currentUser.currentZoneId ? this.defaultAudibleRadius : this.defaultAudibleRadius;

    for (const other of allUsers) {
      if (other.userId === currentUser.userId) continue;

      // Other user is in a private room -> isolated from open floor
      if (other.currentRoomId) continue;

      const dx = currentUser.position.x - other.position.x;
      const dy = currentUser.position.y - other.position.y;
      const distance = Math.sqrt(dx * dx + dy * dy);

      if (distance <= radius) {
        nearby.push(other.userId);
      }
    }

    return nearby;
  }
}
