class QuotaModel {
  final int id;
  final String hotelName;
  final String? location;
  final String? startDate;
  final String? endDate;
  final int? numberOfGuests;
  final double? rentalPrice;
  final int? numberOfRooms;
  final bool isFractioned;
  final QuotaHotel? hotel;
  final String? observations;

  QuotaModel({
    required this.id,
    required this.hotelName,
    this.location,
    this.startDate,
    this.endDate,
    this.numberOfGuests,
    this.rentalPrice,
    this.numberOfRooms,
    this.isFractioned = false,
    this.hotel,
    this.observations,
  });

  String? get firstImageUrl => hotel?.images?.isNotEmpty == true ? hotel!.images!.first : null;

  factory QuotaModel.fromJson(Map<String, dynamic> json) {
    return QuotaModel(
      id: json['id'] as int,
      hotelName: json['hotel_name'] as String? ?? '',
      location: json['location'] as String?,
      startDate: json['start_date'] as String?,
      endDate: json['end_date'] as String?,
      numberOfGuests: json['number_of_guests'] as int?,
      rentalPrice: (json['rental_price'] as num?)?.toDouble(),
      numberOfRooms: json['number_of_rooms'] as int?,
      isFractioned: json['is_fractioned'] as bool? ?? false,
      hotel: json['hotel'] != null ? QuotaHotel.fromJson(json['hotel'] as Map<String, dynamic>) : null,
      observations: json['observations'] as String?,
    );
  }
}

class QuotaHotel {
  final int id;
  final String name;
  final String? city;
  final String? state;
  final List<String>? images;

  QuotaHotel({
    required this.id,
    required this.name,
    this.city,
    this.state,
    this.images,
  });

  factory QuotaHotel.fromJson(Map<String, dynamic> json) {
    final imagesRaw = json['images'];
    List<String>? images;
    if (imagesRaw is List) {
      images = imagesRaw.map((e) => e.toString()).toList();
    }
    return QuotaHotel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      city: json['city'] as String?,
      state: json['state'] as String?,
      images: images,
    );
  }
}
