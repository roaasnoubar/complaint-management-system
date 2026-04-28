class DashboardStatsModel {
  final String satisfactionRate;
  final String responseTime;
  final String processedComplaints;
  final String userName;

  DashboardStatsModel({
    required this.satisfactionRate,
    required this.responseTime,
    required this.processedComplaints,
    required this.userName,
  });

  factory DashboardStatsModel.fromJson(Map<String, dynamic> json) {
    return DashboardStatsModel(
      satisfactionRate: json['satisfaction_rate'] ?? "0%",
      responseTime: json['response_time'] ?? "0",
      processedComplaints: json['processed_count'] ?? "0",
      userName: json['user_name'] ?? "",
    );
  }
}
