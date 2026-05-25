class OfferModel {
  final int id;
  final String title;
  final String? city;
  final String? state;
  final String? startDate;
  final String? endDate;
  final int? numberOfDays;
  final int? numberOfPeople;
  final double? price;
  final bool isAuction;
  final bool isFractioned;
  final OfferHotel? hotel;
  final String? description;
  final List<String>? photos;

  OfferModel({
    required this.id,
    required this.title,
    this.city,
    this.state,
    this.startDate,
    this.endDate,
    this.numberOfDays,
    this.numberOfPeople,
    this.price,
    this.isAuction = false,
    this.isFractioned = false,
    this.hotel,
    this.description,
    this.photos,
  });

  String? get firstImageUrl {
    if (photos != null && photos!.isNotEmpty) return photos!.first;
    return hotel?.images?.isNotEmpty == true ? hotel!.images!.first : null;
  }

  factory OfferModel.fromJson(Map<String, dynamic> json) {
    final hotelRaw = json['hotel'];
    OfferHotel? hotel;
    if (hotelRaw is Map<String, dynamic>) {
      hotel = OfferHotel.fromJson(hotelRaw);
    }
    List<String>? photos;
    final photosRaw = json['photos'];
    if (photosRaw is List) {
      photos = photosRaw.map((e) => e.toString()).toList();
    }
    return OfferModel(
      id: json['id'] as int,
      title: json['title'] as String? ?? 'Oferta de Aluguel',
      city: json['city'] as String?,
      state: json['state'] as String?,
      startDate: json['start_date'] as String?,
      endDate: json['end_date'] as String?,
      numberOfDays: json['number_of_days'] as int?,
      numberOfPeople: json['number_of_people'] as int?,
      price: (json['price'] as num?)?.toDouble(),
      isAuction: json['is_auction'] as bool? ?? false,
      isFractioned: json['is_fractioned'] as bool? ?? false,
      hotel: hotel,
      description: json['description'] as String?,
      photos: photos,
    );
  }
}

class OfferHotel {
  final int id;
  final String name;
  final String? city;
  final String? state;
  final List<String>? images;

  OfferHotel({
    required this.id,
    required this.name,
    this.city,
    this.state,
    this.images,
  });

  factory OfferHotel.fromJson(Map<String, dynamic> json) {
    final imagesRaw = json['images'];
    List<String>? images;
    if (imagesRaw is List) {
      images = imagesRaw.map((e) => e.toString()).toList();
    }
    return OfferHotel(
      id: json['id'] as int,
      name: json['name'] as String? ?? '',
      city: json['city'] as String?,
      state: json['state'] as String?,
      images: images,
    );
  }
}
